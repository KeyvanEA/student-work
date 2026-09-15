<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RejectTaskRequest;
use App\Models\Notification;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class AdminTaskController extends Controller
{
    public function index(Request $request)
    {
        // بدون پارامتر، فهرست روی تسک‌های منتظر بررسی متمرکز است.
        $status = $request->query('status', 'pending');
        if (!in_array($status, ['pending', 'open', 'rejected', 'all'], true)) {
            return response()->json(['message' => 'وضعیت تسک نامعتبر است.'], 422);
        }
        $tasks = Task::query()
            ->select([
                'id',
                'user_id',
                'category_id',
                'title',
                'budget',
                'deadline',
                'status',
                'created_at',
            ])
            ->with([
                'user:id,full_name',
                'category:id,name',
            ]);
        if ($status !== 'all') {
            $tasks->where('status', $status);
        }
        $tasks = $tasks->latest()->paginate(10);
        return response()->json(['tasks' => $tasks], 200);
    }

    public function show(Task $task)
    {
        $task->load([
            'user:id,full_name,avatar,mobile,student_number,field_of_study,university_name',
            'category:id,name',
            'skills:id,name',
            'files:id,task_id,file_path',
        ]);

        $task->files->transform(function ($file) {
            $file->download_url = url(Storage::url($file->file_path));
            return $file;
        });

        return response()->json([
            'task' => $task,
        ], 200);
    }

    public function approve(Request $request, Task $task)
    {
        $user = $request->user();

        if ($task->status !== 'pending') {
            return response()->json(['message' => 'این تسک در حالت انتظار بررسی نمی‌باشد.'], 422);
        }

        try {
            DB::beginTransaction();

            $task = Task::where('id', $task->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($task->status !== 'pending') {
                DB::rollBack();
                return response()->json(['message' => 'این تسک در حالت انتظار بررسی نمی‌باشد.'], 422);
            }

            $task->status = 'open';
            $task->rejection_reason = null;
            $task->save();

            DB::commit();
        }
        catch (\Exception $exception){
            DB::rollBack();
            Log::error('Approve Task Failed', [
                'user_id' => $user->id,
                'task_id' => $task->id,
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTraceAsString(),
            ]);
            return response()->json([
                'message' => 'خطایی در تایید تسک رخ داد. لطفاً دوباره تلاش کنید.'
            ], 500);
        }

        // اعلان بعد از ثبت قطعی تغییر وضعیت ساخته می‌شود تا شکست آن، تایید تسک را برنگرداند.
        try {
            Notification::create([
                'user_id' => $task->user_id,
                'title' => 'انتشار تسک',
                'message' => 'تسک شما توسط ادمین بررسی شد و با موفقیت منتشر شد. عنوان تسک: ' . $task->title,
                'is_read' => false,
            ]);
        }
        catch (\Exception $exception){
            Log::error('Approve Task notification creation failed', [
                'user_id' => $user->id,
                'task_id' => $task->id,
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
            ]);
        }

        return response()->json([
            'message' => 'تسک با موفقیت تایید و منتشر شد.',
            'task' => ['id' => $task->id, 'status' => $task->status],
        ], 200);
    }

    public function reject(RejectTaskRequest $request, Task $task)
    {
        $user = $request->user();

        if ($task->status !== 'pending') {
            return response()->json(['message' => 'این تسک در حالت انتظار بررسی نمی‌باشد.'], 422);
        }

        $validated = $request->validated();

        try {
            DB::beginTransaction();

            $task = Task::where('id', $task->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($task->status !== 'pending') {
                DB::rollBack();
                return response()->json(['message' => 'این تسک در حالت انتظار بررسی نمی‌باشد.'], 422);
            }

            $task->status = 'rejected';
            $task->rejection_reason = $validated['rejection_reason'];
            $task->save();

            DB::commit();
        }
        catch (\Exception $exception){
            DB::rollBack();
            Log::error('Reject Task Failed', [
                'user_id' => $user->id,
                'task_id' => $task->id,
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTraceAsString(),
            ]);
            return response()->json([
                'message' => 'خطایی در رد کردن تسک رخ داد. لطفاً دوباره تلاش کنید.'
            ], 500);
        }

        try {
            Notification::create([
                'user_id' => $task->user_id,
                'title' => 'عدم انتشار تسک',
                'message' => 'تسک شما توسط ادمین بررسی شد اما منتشر نشد. عنوان تسک: ' . $task->title .
                    '. دلیل ادمین: ' . $validated['rejection_reason'],
                'is_read' => false,
            ]);
        }
        catch (\Exception $exception){
            Log::error('Reject Task notification creation failed', [
                'user_id' => $user->id,
                'task_id' => $task->id,
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
            ]);
        }

        return response()->json([
            'message' => 'تسک رد شد و برای کاربر منتشر نمی‌شود.',
            'task' => ['id' => $task->id, 'status' => $task->status],
        ], 200);
    }
}
