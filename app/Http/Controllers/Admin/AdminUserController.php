<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OtpCode;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AdminUserController extends Controller
{
    public function index(Request $request)
    {
        $mobile = trim((string) $request->query('mobile', ''));
        if ($mobile !== '' && !preg_match('/^[0-9]{1,11}$/', $mobile)) {
            return response()->json(['message' => 'شماره موبایل وارد شده معتبر نیست.'], 422);
        }
        $users = User::query()
            ->select([
                'id',
                'full_name',
                'mobile',
                'student_number',
                'is_active',
                'created_at',
            ])
            ->with(['roles:id,name']);
        if ($mobile !== '') {
            $users->where('mobile', 'like', "%{$mobile}%");
        }
        $users = $users->latest()->paginate(10);
        return response()->json(['users' => $users], 200);
    }

    /**
     * حذف کاربر توسط ادمین.
     *
     * همه کلیدهای خارجی به users در این پروژه cascadeOnDelete هستند، یعنی حذف فیزیکی
     * یک کاربر، تسک‌ها و درخواست‌های همکاری و از آن‌جا پروژه‌ها، تحویل‌ها، شکایات و
     * نظرات طرف مقابل را هم پاک می‌کند. برای همین:
     *
     *  - کاربری که در هیچ پروژه‌ای (نه به‌عنوان کارفرما و نه کارجو) نیست، واقعاً حذف می‌شود.
     *  - کاربری که سابقه پروژه دارد حذف فیزیکی نمی‌شود و به‌جای آن با فیلد موجود
     *    is_active غیرفعال می‌شود تا تاریخچه طرف مقابل از بین نرود.
     */
    public function destroy(Request $request, User $user)
    {
        $admin = $request->user();

        if ($admin->id === $user->id) {
            return response()->json(['message' => 'حساب خودتان را نمی‌توانید حذف کنید.'], 422);
        }

        if ($user->hasRole('admin')) {
            return response()->json(['message' => 'حساب ادمین از این بخش قابل حذف نیست.'], 422);
        }

        try {
            DB::beginTransaction();

            $user = User::where('id', $user->id)
                ->lockForUpdate()
                ->firstOrFail();

            $hasProjectHistory = Project::query()
                ->whereHas('application', function ($application) use ($user) {
                    $application->where('user_id', $user->id)
                        ->orWhereHas('task', function ($task) use ($user) {
                            $task->where('user_id', $user->id);
                        });
                })
                ->exists();

            // توکن‌های sanctum کلید خارجی ندارند و باید دستی پاک شوند.
            $user->tokens()->delete();

            if ($hasProjectHistory) {
                $user->is_active = false;
                $user->save();

                DB::commit();

                return response()->json([
                    'message' => 'این کاربر سابقه پروژه دارد و حذف کامل آن تاریخچه طرف مقابل را هم پاک می‌کرد؛ به همین دلیل حساب او غیرفعال شد.',
                    'strategy' => 'deactivated',
                    'user' => ['id' => $user->id, 'is_active' => $user->is_active],
                ], 200);
            }

            // otp_codes با شماره موبایل نگهداری می‌شود و کلید خارجی ندارد.
            OtpCode::where('mobile', $user->mobile)->delete();
            $user->delete();

            DB::commit();

            return response()->json([
                'message' => 'کاربر مورد نظر با موفقیت حذف شد.',
                'strategy' => 'deleted',
            ], 200);
        }
        catch (\Exception $exception){
            DB::rollBack();
            Log::error('Admin Delete User Failed', [
                'user_id' => $admin->id,
                'target_user_id' => $user->id,
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTraceAsString(),
            ]);
            return response()->json([
                'message' => 'خطایی در حذف کاربر رخ داد. لطفاً دوباره تلاش کنید.'
            ], 500);
        }
    }
}
