<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTaskRequest;
use App\Models\Task;
use App\Models\TaskFile;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class TaskController extends Controller
{
    public function store(StoreTaskRequest $request)
    {
       $validatedData = $request->validated();
        $uploadedFiles = [];
       $user = $request->user();

        try {
            DB::beginTransaction();
            $taskData = Arr::only($validatedData,[
                'title',
                'description',
                'budget',
                'deadline',
                'category_id',
            ]);
            $task = $user->tasks()->create($taskData);
            $task->skills()->sync($validatedData['skills']);

            if ($request->hasFile('files')){
                foreach ($request->file('files') as $file){
                    $filePath =  $file->store("tasks/{$task->id}/attachments");
                    $uploadedFiles[] = $filePath;
//                    throw new \Exception('Transaction Test');
                    $task->files()->create([
                        'file_path'=> $filePath,
                    ]);

                }
            }
            DB::commit();
            $task->load('skills','category','files');
            return response()->json(['message' => 'تسک شما با موفقیت ثبت شد', 'task' => $task],201);
        }
        catch (\Exception $exception){
            DB::rollBack();
            Log::error('Task Store Failed', [
                'user_id' => $user->id,
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTraceAsString(),
            ]);
            foreach ($uploadedFiles as $filePath){
                Storage::delete($filePath);
            }
            return response()->json([
                'message' => 'خطایی در ثبت تسک رخ داد. لطفاً دوباره تلاش کنید.'
            ], 500);
        }
    }
    public function index()
    {
        $tasks = Task::query()
            ->select(['id', 'user_id', 'title', 'budget', 'created_at'])
            ->where('status', 'open')
            ->with(['user:id,full_name'])
            ->latest()
            ->paginate(10);
        return response()->json([
            'tasks' => $tasks,
        ], 200);
    }

    public function show($id)
    {

        $task = Task::query()
            ->with([
                'user:id,full_name,avatar',
                'category:id,name',
                'skills:id,name',
                'files:id,task_id,file_path',
            ])
            ->findOrFail($id);

        $task->files->transform(function ($file) use ($task) {
            $file->download_url = route('tasks.files.download', [
                'task' => $task->id,
                'file' => $file->id,
            ]);
            return $file;
        });

        return response()->json([
            'task' => $task,
        ], 200);
    }

    public function downloadFile(Request $request, Task $task, TaskFile $file)
    {
        if ($file->task_id !== $task->id) {
            return response()->json(['message' => 'فایل موردنظر پیدا نشد.'], 404);
        }
        if (!Storage::exists($file->file_path)) {
            return response()->json(['message' => 'فایل موردنظر پیدا نشد.'], 404);
        }

        return response(Storage::get($file->file_path), 200)
            ->header('Content-Type', Storage::mimeType($file->file_path) ?: 'application/octet-stream')
            ->header('Content-Disposition', 'attachment; filename="' . basename($file->file_path) . '"');
    }

    public function cancel(Request $request, Task $task)
    {
        if ($request->user()->id != $task->user_id)
            return response()->json(['message'=> 'شما صاحب تسک نمی باشید.'],403);

        if($task->status !== 'open')
            return response()->json(['message'=>'تسک باز نیست قادر به لغو آن نیستید.'],409);

        $task->status ='cancelled';
        $task->save();
        return response()->json([
            'message' => 'تسک با موفقیت لغو شد.',
            'task' => $task,
        ], 200);
    }

}
