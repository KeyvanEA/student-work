<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\Delivery;
use App\Models\Project;
use App\Models\User;
use Database\Seeders\CategorySeeder;
use Database\Seeders\SkillSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ApiFlowTest extends TestCase
{
    use RefreshDatabase;

    private User $employer;
    private User $worker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CategorySeeder::class);
        $this->seed(SkillSeeder::class);

        $this->employer = User::factory()->create(['mobile' => '09120000001']);
        $this->worker   = User::factory()->create(['mobile' => '09120000002']);
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
     * گارد auth کاربر حل‌شده را برای طول عمر اپ در همین تست کش می‌کند، بنابراین
     * قبل از هر درخواست باید پاک شود تا جابه‌جایی بین کاربران واقعاً اعمال شود.
     */
    private function as(User $user): array
    {
        $this->app['auth']->forgetGuards();

        return ['Authorization' => 'Bearer ' . ($this->tokens[$user->id] ??= $this->tokenFor($user))];
    }

    /** @var array<int, string> */
    private array $tokens = [];

    public function test_categories_endpoint_is_public(): void
    {
        $this->getJson('/api/categories')
            ->assertOk()
            ->assertJsonStructure(['categories' => [['id', 'name', 'slug']]]);
    }

    public function test_full_employer_and_worker_flow(): void
    {
        Storage::fake();

        // ---- employer creates a task ----
        $create = $this->post('/api/tasks', [
            'title' => 'حل تمرین ساختمان داده',
            'description' => 'حل کامل تمرین‌های فصل ۳ و ۴.',
            'budget' => 3500000,
            'deadline' => now()->addDays(5)->format('Y-m-d H:i:s'),
            'category_id' => 1,
            'skills' => [1],
        ], $this->as($this->employer))->assertCreated();
        $taskId = $create->json('task.id');

        // ---- my tasks ----
        $this->getJson('/api/users/me/tasks', $this->as($this->employer))
            ->assertOk()
            ->assertJsonPath('tasks.total', 1)
            ->assertJsonPath('tasks.data.0.applications_count', 0);

        // ---- worker applies ----
        $this->post("/api/tasks/{$taskId}/applications", [
            'description' => 'این درس را گذرانده‌ام و می‌توانم انجام دهم.',
        ], $this->as($this->worker))->assertCreated();

        $applicationId = Application::where('task_id', $taskId)->firstOrFail()->id;

        // ---- my applications ----
        $this->getJson('/api/users/me/applications', $this->as($this->worker))
            ->assertOk()
            ->assertJsonPath('applications.total', 1)
            ->assertJsonPath('applications.data.0.task.title', 'حل تمرین ساختمان داده');

        // ---- applicant can now read back their own application ----
        $this->getJson("/api/applications/{$applicationId}", $this->as($this->worker))->assertOk();

        // ---- accept returns project_id ----
        $accept = $this->patchJson("/api/applications/{$applicationId}/accept", [], $this->as($this->employer))
            ->assertOk()
            ->assertJsonStructure(['message', 'project_id', 'project']);
        $projectId = $accept->json('project_id');
        $this->assertNotNull($projectId);

        // ---- projects list, visible to both sides ----
        $this->getJson('/api/projects', $this->as($this->employer))->assertOk()->assertJsonPath('projects.total', 1);
        $this->getJson('/api/projects', $this->as($this->worker))->assertOk()->assertJsonPath('projects.total', 1);

        // ---- worker submits a delivery ----
        $this->post("/api/projects/{$projectId}/deliveries", [
            'description' => 'فایل خروجی تمرین‌ها ضمیمه شد.',
            'files' => [UploadedFile::fake()->create('homework.pdf', 40, 'application/pdf')],
        ], $this->as($this->worker))->assertCreated();

        // ---- deliveries list ----
        $list = $this->getJson("/api/projects/{$projectId}/deliveries", $this->as($this->employer))
            ->assertOk()
            ->assertJsonStructure(['deliveries' => ['data' => [['id', 'status', 'files' => [['preview_url', 'download_url']]]]]]);
        $deliveryId = $list->json('deliveries.data.0.id');

        // an unrelated user is refused
        $stranger = User::factory()->create(['mobile' => '09120000009']);
        $this->getJson("/api/projects/{$projectId}/deliveries", $this->as($stranger))->assertForbidden();

        // ---- delivery show carries project context ----
        $show = $this->getJson("/api/deliveries/{$deliveryId}", $this->as($this->employer))->assertOk();
        $show->assertJsonPath('delivery.project_id', $projectId)
            ->assertJsonPath('delivery.viewer_role', 'employer')
            ->assertJsonPath('delivery.can_download', false)
            ->assertJsonPath('delivery.project.payment_status', 'unpaid');

        $this->getJson("/api/deliveries/{$deliveryId}", $this->as($this->worker))
            ->assertJsonPath('delivery.viewer_role', 'worker')
            ->assertJsonPath('delivery.can_download', true);

        $fileId = $show->json('delivery.files.0.id');

        // ---- employer cannot download before paying ----
        $this->get("/api/deliveries/{$deliveryId}/files/{$fileId}/download", $this->as($this->employer))
            ->assertForbidden();

        // ---- accept the delivery, then pay ----
        $this->patchJson("/api/deliveries/{$deliveryId}/accept", [], $this->as($this->employer))->assertOk();
        $this->assertSame('completed', Project::find($projectId)->status);

        $this->patchJson("/api/projects/{$projectId}/payment", [], $this->as($this->employer))->assertOk();
        $this->assertSame('paid', Project::find($projectId)->fresh()->payment_status);

        // ---- now the download works and can_download flips ----
        $this->getJson("/api/deliveries/{$deliveryId}", $this->as($this->employer))
            ->assertJsonPath('delivery.can_download', true);
        $this->get("/api/deliveries/{$deliveryId}/files/{$fileId}/download", $this->as($this->employer))
            ->assertOk();
    }

    public function test_reject_application_succeeds(): void
    {

        $task = $this->employer->tasks()->create([
            'category_id' => 1,
            'title' => 'تسک نمونه',
            'description' => 'توضیح',
            'budget' => 500000,
            'deadline' => now()->addDays(3),
            'status' => 'open',
        ]);
        $application = Application::create([
            'user_id' => $this->worker->id,
            'task_id' => $task->id,
            'description' => 'درخواست همکاری',
            'status' => 'pending',
        ]);

        $this->patchJson("/api/applications/{$application->id}/reject", [], $this->as($this->employer))
            ->assertOk()
            ->assertJsonPath('message', 'این درخواست همکاری با موفقیت رد شد.');

        $this->assertSame('rejected', $application->fresh()->status);
    }

    public function test_reject_delivery_records_reason(): void
    {
        Storage::fake();
        $task = $this->employer->tasks()->create([
            'category_id' => 1,
            'title' => 'تسک نمونه دوم', 'description' => 'توضیح',
            'budget' => 500000, 'deadline' => now()->addDays(3), 'status' => 'open',
        ]);
        $application = Application::create([
            'user_id' => $this->worker->id, 'task_id' => $task->id,
            'description' => 'درخواست', 'status' => 'pending',
        ]);
        $this->patchJson("/api/applications/{$application->id}/accept", [], $this->as($this->employer))->assertOk();
        $projectId = Project::firstOrFail()->id;

        $this->post("/api/projects/{$projectId}/deliveries", [
            'description' => 'تحویل اول پروژه.',
            'files' => [UploadedFile::fake()->create('a.pdf', 10, 'application/pdf')],
        ], $this->as($this->worker))->assertCreated();

        $deliveryId = Delivery::firstOrFail()->id;

        $this->patchJson("/api/deliveries/{$deliveryId}/reject", [
            'rejection_reason' => 'بخش دوم تمرین ناقص است و باید کامل شود.',
        ], $this->as($this->employer))->assertOk();

        $this->getJson("/api/deliveries/{$deliveryId}", $this->as($this->employer))
            ->assertJsonPath('delivery.status', 'rejected')
            ->assertJsonPath('delivery.rejection_reason', 'بخش دوم تمرین ناقص است و باید کامل شود.');
    }

    public function test_otp_login_flow(): void
    {
        config(['app.debug' => true]);

        $send = $this->postJson('/api/auth/send-otp', ['mobile' => $this->worker->mobile])
            ->assertOk()
            ->assertJsonStructure(['message', 'expires_in', 'code']);
        $code = $send->json('code');

        $this->postJson('/api/auth/verify-otp', ['mobile' => $this->worker->mobile, 'code' => '000000'])
            ->assertStatus(422);

        $this->postJson('/api/auth/verify-otp', ['mobile' => $this->worker->mobile, 'code' => $code])
            ->assertOk()
            ->assertJsonStructure(['message', 'user', 'token']);

        // کد یک‌بارمصرف است
        $this->postJson('/api/auth/verify-otp', ['mobile' => $this->worker->mobile, 'code' => $code])
            ->assertStatus(422);

        // شماره ناشناس
        $this->postJson('/api/auth/send-otp', ['mobile' => '09999999999'])->assertStatus(401);
    }

    public function test_task_and_resume_file_downloads(): void
    {
        Storage::fake();

        $create = $this->post('/api/tasks', [
            'title' => 'تسک با پیوست',
            'description' => 'توضیح تسک',
            'budget' => 500000,
            'deadline' => now()->addDays(4)->format('Y-m-d H:i:s'),
            'category_id' => 1,
            'skills' => [1],
            'files' => [UploadedFile::fake()->create('spec.pdf', 12, 'application/pdf')],
        ], $this->as($this->employer))->assertCreated();

        $taskId = $create->json('task.id');
        $show = $this->getJson("/api/tasks/{$taskId}")->assertOk();
        $downloadUrl = $show->json('task.files.0.download_url');
        $this->assertStringContainsString("/api/tasks/{$taskId}/files/", $downloadUrl);

        $this->get($downloadUrl, $this->as($this->employer))->assertOk();

        // گارد auth کاربر را برای طول عمر اپ در همین تست کش می‌کند
        $this->app['auth']->forgetGuards();
        $this->getJson($downloadUrl)->assertUnauthorized();

        // no resume uploaded yet
        $this->getJson('/api/profile/resume', $this->as($this->employer))->assertNotFound();
    }
}
