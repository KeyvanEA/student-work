<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\Notification;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Database\Seeders\AdminSeeder;
use Database\Seeders\CategorySeeder;
use Database\Seeders\SkillSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminUserAndTaskModerationTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $employer;
    private User $worker;

    /** @var array<int, string> */
    private array $tokens = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CategorySeeder::class);
        $this->seed(SkillSeeder::class);
        $this->seed(AdminSeeder::class);

        $this->admin    = User::where('mobile', '09938746857')->firstOrFail();
        $this->employer = $this->makeUser('کارفرمای تست', '09120000001', '401000001');
        $this->worker   = $this->makeUser('کارجوی تست', '09120000002', '401000002');
    }

    /**
     * UserFactory روی main فیلدهای اجباری full_name / mobile / student_number را
     * کامنت‌شده دارد، بنابراین اینجا صریح داده می‌شوند تا تست به فکتور وابسته نباشد.
     */
    private function makeUser(string $fullName, string $mobile, string $studentNumber): User
    {
        return User::factory()->create([
            'full_name' => $fullName,
            'mobile' => $mobile,
            'student_number' => $studentNumber,
        ]);
    }

    private function tokenFor(User $user): string
    {
        $this->app['auth']->forgetGuards();

        return $this->postJson('/api/login', ['mobile' => $user->mobile])
            ->assertOk()
            ->json('token');
    }

    /**
     * هدر احراز هویت برای یک درخواست.
     * گارد auth کاربر حل‌شده را کش می‌کند، بنابراین قبل از هر درخواست باید پاک شود
     * تا جابه‌جایی بین کاربرها واقعاً اعمال شود.
     */
    private function as(User $user): array
    {
        $this->app['auth']->forgetGuards();

        return ['Authorization' => 'Bearer ' . ($this->tokens[$user->id] ??= $this->tokenFor($user))];
    }

    private function guest(): array
    {
        $this->app['auth']->forgetGuards();

        return [];
    }

    /** یک تسک تازه که طبق چرخه جدید در انتظار بررسی ادمین است */
    private function createPendingTask(): int
    {
        return $this->post('/api/tasks', [
            'title' => 'حل تمرین ساختمان داده',
            'description' => 'حل کامل تمرین‌های فصل ۳ و ۴ به همراه توضیح.',
            'budget' => 3500000,
            'deadline' => now()->addDays(5)->format('Y-m-d H:i:s'),
            'category_id' => 1,
            'skills' => [1],
        ], $this->as($this->employer))
            ->assertCreated()
            ->json('task.id');
    }

    // ---------------------------------------------------------------- تسک‌ها

    public function test_new_task_waits_for_admin_and_is_hidden_from_public(): void
    {
        $taskId = $this->createPendingTask();

        $this->assertSame('pending', Task::findOrFail($taskId)->status);

        // فهرست عمومی تسک‌ها نباید آن را برگرداند
        $this->getJson('/api/tasks', $this->guest())
            ->assertOk()
            ->assertJsonPath('tasks.total', 0);

        // نمایش عمومی تسک برای مهمان و کاربر دیگر بسته است
        $this->getJson("/api/tasks/{$taskId}", $this->guest())->assertNotFound();
        $this->getJson("/api/tasks/{$taskId}", $this->as($this->worker))->assertNotFound();

        // ولی صاحب تسک و ادمین همچنان می‌بینندش
        $this->getJson("/api/tasks/{$taskId}", $this->as($this->employer))
            ->assertOk()
            ->assertJsonPath('task.status', 'pending');
        $this->getJson("/api/tasks/{$taskId}", $this->as($this->admin))->assertOk();

        // تسک در انتظار بررسی نباید درخواست همکاری بپذیرد
        $this->post("/api/tasks/{$taskId}/applications", [
            'description' => 'این درس را گذرانده‌ام و می‌توانم انجام دهم.',
        ], $this->as($this->worker))->assertStatus(409);

        // صاحب تسک باید تسک خودش را در فهرست تسک‌های ثبت‌شده ببیند
        $this->getJson('/api/tasks/mine', $this->as($this->employer))
            ->assertOk()
            ->assertJsonPath('tasks.total', 1)
            ->assertJsonPath('tasks.data.0.status', 'pending');
    }

    public function test_only_admin_can_use_task_moderation_endpoints(): void
    {
        $taskId = $this->createPendingTask();

        $this->getJson('/api/admin/tasks', $this->guest())->assertUnauthorized();
        $this->getJson('/api/admin/tasks', $this->as($this->worker))->assertForbidden();
        $this->getJson("/api/admin/tasks/{$taskId}", $this->as($this->worker))->assertForbidden();
        $this->patchJson("/api/admin/tasks/{$taskId}/approve", [], $this->guest())->assertUnauthorized();
        $this->patchJson("/api/admin/tasks/{$taskId}/approve", [], $this->as($this->worker))->assertForbidden();
    }

    public function test_admin_sees_pending_tasks_and_task_detail(): void
    {
        $taskId = $this->createPendingTask();

        $this->getJson('/api/admin/tasks', $this->as($this->admin))
            ->assertOk()
            ->assertJsonPath('tasks.total', 1)
            ->assertJsonPath('tasks.data.0.id', $taskId)
            ->assertJsonPath('tasks.data.0.status', 'pending')
            ->assertJsonStructure(['tasks' => ['data' => [[
                'id', 'title', 'budget', 'deadline', 'status', 'created_at', 'user', 'category',
            ]]]]);

        $this->getJson('/api/admin/tasks?status=open', $this->as($this->admin))
            ->assertOk()
            ->assertJsonPath('tasks.total', 0);

        $this->getJson('/api/admin/tasks?status=unknown', $this->as($this->admin))
            ->assertStatus(422);

        $this->getJson("/api/admin/tasks/{$taskId}", $this->as($this->admin))
            ->assertOk()
            ->assertJsonPath('task.id', $taskId)
            ->assertJsonStructure(['task' => ['id', 'title', 'description', 'budget', 'deadline',
                'status', 'created_at', 'user', 'category', 'skills', 'files']]);

        $this->getJson('/api/admin/tasks/999999', $this->as($this->admin))->assertNotFound();
    }

    public function test_admin_approve_publishes_task_and_notifies_employer(): void
    {
        $taskId = $this->createPendingTask();

        $this->patchJson("/api/admin/tasks/{$taskId}/approve", [], $this->as($this->admin))
            ->assertOk()
            ->assertJsonPath('task.status', 'open');

        $this->assertSame('open', Task::findOrFail($taskId)->status);

        // حالا تسک واقعاً عمومی است
        $this->getJson('/api/tasks', $this->guest())
            ->assertOk()
            ->assertJsonPath('tasks.total', 1);
        $this->getJson("/api/tasks/{$taskId}", $this->guest())->assertOk();

        // و درخواست همکاری می‌پذیرد
        $this->post("/api/tasks/{$taskId}/applications", [
            'description' => 'این درس را گذرانده‌ام و می‌توانم انجام دهم.',
        ], $this->as($this->worker))->assertCreated();

        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->employer->id,
            'title' => 'انتشار تسک',
        ]);

        // تایید دوباره یک تسک منتشرشده مجاز نیست
        $this->patchJson("/api/admin/tasks/{$taskId}/approve", [], $this->as($this->admin))
            ->assertStatus(422);
    }

    public function test_admin_reject_requires_reason_and_keeps_task_hidden(): void
    {
        $taskId = $this->createPendingTask();

        // دلیل رد کردن اجباری است
        $this->patchJson("/api/admin/tasks/{$taskId}/reject", [], $this->as($this->admin))
            ->assertStatus(422)
            ->assertJsonValidationErrors('rejection_reason');

        $this->patchJson("/api/admin/tasks/{$taskId}/reject", ['rejection_reason' => '   '], $this->as($this->admin))
            ->assertStatus(422)
            ->assertJsonValidationErrors('rejection_reason');

        $this->patchJson("/api/admin/tasks/{$taskId}/reject", ['rejection_reason' => 'کوتاه'], $this->as($this->admin))
            ->assertStatus(422)
            ->assertJsonValidationErrors('rejection_reason');

        $reason = 'شرح تسک ناقص است و بودجه با حجم کار همخوانی ندارد.';
        $this->patchJson("/api/admin/tasks/{$taskId}/reject", ['rejection_reason' => $reason], $this->as($this->admin))
            ->assertOk()
            ->assertJsonPath('task.status', 'rejected');

        $task = Task::findOrFail($taskId);
        $this->assertSame('rejected', $task->status);
        $this->assertSame($reason, $task->rejection_reason);

        // ردشده هم مثل pending عمومی نیست و درخواست همکاری نمی‌پذیرد
        $this->getJson('/api/tasks', $this->guest())
            ->assertOk()
            ->assertJsonPath('tasks.total', 0);
        $this->getJson("/api/tasks/{$taskId}", $this->guest())->assertNotFound();
        $this->getJson("/api/tasks/{$taskId}", $this->as($this->worker))->assertNotFound();
        $this->getJson("/api/tasks/{$taskId}", $this->as($this->employer))
            ->assertOk()
            ->assertJsonPath('task.rejection_reason', $reason);

        $this->post("/api/tasks/{$taskId}/applications", [
            'description' => 'این درس را گذرانده‌ام و می‌توانم انجام دهم.',
        ], $this->as($this->worker))->assertStatus(409);

        // اعلان رد شدن همراه دلیل ادمین ساخته می‌شود
        $notification = Notification::where('user_id', $this->employer->id)
            ->where('title', 'عدم انتشار تسک')
            ->firstOrFail();
        $this->assertStringContainsString($reason, $notification->message);

        // رد کردن دوباره یا تایید یک تسک ردشده مجاز نیست
        $this->patchJson("/api/admin/tasks/{$taskId}/reject", ['rejection_reason' => $reason], $this->as($this->admin))
            ->assertStatus(422);
        $this->patchJson("/api/admin/tasks/{$taskId}/approve", [], $this->as($this->admin))
            ->assertStatus(422);
    }

    public function test_assigned_task_can_not_be_moderated(): void
    {
        $taskId = $this->createPendingTask();
        $this->patchJson("/api/admin/tasks/{$taskId}/approve", [], $this->as($this->admin))->assertOk();

        $this->post("/api/tasks/{$taskId}/applications", [
            'description' => 'این درس را گذرانده‌ام و می‌توانم انجام دهم.',
        ], $this->as($this->worker))->assertCreated();

        $applicationId = Application::where('task_id', $taskId)->firstOrFail()->id;
        $this->patchJson("/api/applications/{$applicationId}/accept", [], $this->as($this->employer))->assertOk();

        $this->assertSame('assigned', Task::findOrFail($taskId)->status);

        $this->patchJson("/api/admin/tasks/{$taskId}/approve", [], $this->as($this->admin))->assertStatus(422);
        $this->patchJson("/api/admin/tasks/{$taskId}/reject", [
            'rejection_reason' => 'این تسک نباید تغییر وضعیت بدهد.',
        ], $this->as($this->admin))->assertStatus(422);

        $this->assertSame('assigned', Task::findOrFail($taskId)->status);
    }

    // --------------------------------------------------------------- کاربران

    public function test_only_admin_can_use_user_management_endpoints(): void
    {
        $this->getJson('/api/admin/users', $this->guest())->assertUnauthorized();
        $this->getJson('/api/admin/users', $this->as($this->worker))->assertForbidden();
        $this->deleteJson("/api/admin/users/{$this->worker->id}", [], $this->guest())->assertUnauthorized();
        $this->deleteJson("/api/admin/users/{$this->employer->id}", [], $this->as($this->worker))->assertForbidden();
    }

    public function test_admin_can_list_and_search_users_by_mobile(): void
    {
        $this->getJson('/api/admin/users', $this->as($this->admin))
            ->assertOk()
            ->assertJsonPath('users.total', 3)
            ->assertJsonStructure(['users' => ['data' => [[
                'id', 'full_name', 'mobile', 'student_number', 'is_active', 'created_at', 'roles',
            ]]]]);

        $this->getJson('/api/admin/users?mobile=09120000002', $this->as($this->admin))
            ->assertOk()
            ->assertJsonPath('users.total', 1)
            ->assertJsonPath('users.data.0.id', $this->worker->id);

        $this->getJson('/api/admin/users?mobile=0912', $this->as($this->admin))
            ->assertOk()
            ->assertJsonPath('users.total', 2);

        $this->getJson('/api/admin/users?mobile=09120009999', $this->as($this->admin))
            ->assertOk()
            ->assertJsonPath('users.total', 0);

        $this->getJson('/api/admin/users?mobile=abcd', $this->as($this->admin))->assertStatus(422);
    }

    public function test_admin_can_delete_user_without_project_history(): void
    {
        $this->deleteJson("/api/admin/users/{$this->worker->id}", [], $this->as($this->admin))
            ->assertOk()
            ->assertJsonPath('strategy', 'deleted');

        $this->assertDatabaseMissing('users', ['id' => $this->worker->id]);
        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id' => $this->worker->id,
            'tokenable_type' => User::class,
        ]);
    }

    public function test_user_with_project_history_is_deactivated_instead_of_deleted(): void
    {
        $taskId = $this->createPendingTask();
        $this->patchJson("/api/admin/tasks/{$taskId}/approve", [], $this->as($this->admin))->assertOk();

        $this->post("/api/tasks/{$taskId}/applications", [
            'description' => 'این درس را گذرانده‌ام و می‌توانم انجام دهم.',
        ], $this->as($this->worker))->assertCreated();

        $applicationId = Application::where('task_id', $taskId)->firstOrFail()->id;
        $this->patchJson("/api/applications/{$applicationId}/accept", [], $this->as($this->employer))->assertOk();

        $projectId = Project::firstOrFail()->id;

        // کارفرما (صاحب تسک پروژه‌دار)
        $this->deleteJson("/api/admin/users/{$this->employer->id}", [], $this->as($this->admin))
            ->assertOk()
            ->assertJsonPath('strategy', 'deactivated');

        // کارجو (طرف مقابل همان پروژه)
        $this->deleteJson("/api/admin/users/{$this->worker->id}", [], $this->as($this->admin))
            ->assertOk()
            ->assertJsonPath('strategy', 'deactivated');

        $this->assertDatabaseHas('users', ['id' => $this->employer->id, 'is_active' => false]);
        $this->assertDatabaseHas('users', ['id' => $this->worker->id, 'is_active' => false]);
        // تاریخچه هیچ‌کدام از دو طرف پاک نشده است
        $this->assertDatabaseHas('projects', ['id' => $projectId]);
        $this->assertDatabaseHas('tasks', ['id' => $taskId]);
        $this->assertDatabaseHas('applications', ['id' => $applicationId]);
    }

    public function test_admin_can_not_delete_admins_or_missing_users(): void
    {
        $this->deleteJson("/api/admin/users/{$this->admin->id}", [], $this->as($this->admin))
            ->assertStatus(422);
        $this->assertDatabaseHas('users', ['id' => $this->admin->id]);

        $otherAdmin = $this->makeUser('ادمین دوم', '09120000003', '401000003');
        $otherAdmin->assignRole('admin');
        $this->deleteJson("/api/admin/users/{$otherAdmin->id}", [], $this->as($this->admin))
            ->assertStatus(422);
        $this->assertDatabaseHas('users', ['id' => $otherAdmin->id]);

        $this->deleteJson('/api/admin/users/999999', [], $this->as($this->admin))->assertNotFound();
    }

    // -------------------------------------------------------------- رگرسیون

    public function test_existing_endpoints_still_work(): void
    {
        $this->getJson('/api/user', $this->as($this->employer))->assertOk();
        $this->getJson('/api/dashboard', $this->as($this->employer))->assertOk();
        $this->getJson('/api/profile', $this->as($this->employer))->assertOk();
        $this->getJson('/api/skills', $this->as($this->employer))->assertOk();
        $this->getJson('/api/profile/satisfaction', $this->as($this->employer))->assertOk();
        $this->getJson('/api/applications?type=sent', $this->as($this->employer))->assertOk();
        $this->getJson('/api/projects', $this->as($this->employer))->assertOk();
        $this->getJson('/api/complaints', $this->as($this->employer))->assertOk();
        $this->getJson('/api/complaints/related', $this->as($this->employer))->assertOk();
        $this->getJson('/api/notifications', $this->as($this->employer))->assertOk();
        $this->getJson('/api/admin/dashboard', $this->as($this->admin))->assertOk();
        $this->getJson('/api/admin/complaints', $this->as($this->admin))->assertOk();
        $this->postJson('/api/logout', [], $this->as($this->employer))->assertOk();
    }

    public function test_task_owner_can_still_cancel_task_after_approval(): void
    {
        $taskId = $this->createPendingTask();
        $this->patchJson("/api/admin/tasks/{$taskId}/approve", [], $this->as($this->admin))->assertOk();

        $this->patchJson("/api/tasks/{$taskId}/cancel", [], $this->as($this->employer))
            ->assertOk()
            ->assertJsonPath('task.status', 'cancelled');
    }
}
