<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ApplicationRequest;
use App\Models\Application;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ApplicationController extends Controller
{
    public function store(ApplicationRequest $request, Task $task)
    {
        $user = $request->user();
        $validatedData = $request->validated();
        $uploadedFiles = [];
        if ($task->user_id === $user->id) {
            return response()->json(['message'=>'صاحب تسک نمی تواند درخواست همکاری دهد.'],403);
        }

        if ($task->status !== 'open') {
            return response()->json(['message'=>'قادر به ارسال درخواست همکاری نیستید تسک باز نمی باشد.'],409);
        }

        if (
            $task->applications()
                ->where('user_id', $user->id)
                ->exists())
        {
            return response()->json(['message'=>'شما یک بار درخواست همکاری ارسال کردید منتظر نتیجه درخواست بمانید.'],409);
        }
        try {
            DB::beginTransaction();
            $applicationData = Arr::only($validatedData,[
                'description',
            ]);
            $applicationData['user_id'] = $user->id;
            $applicationData['status'] = 'pending';
            $application = $task->applications()->create($applicationData);


            if (!empty($validatedData['files'])){
                foreach ($validatedData['files'] as $file){
                    $filePath =  $file->store("applications/{$application->id}/attachments");
                    $uploadedFiles[] = $filePath;
//                    throw new \Exception('Transaction Test');
                    $application->files()->create([
                        'file_path'=> $filePath,
                    ]);

                }
            }
            DB::commit();
            $application->load('files');
            return response()->json(['message' => 'درخواست همکاری شما با موفقیت ارسال گردید', 'application' => $application],201);
        }
        catch (\Exception $exception){
            DB::rollBack();
            Log::error('Application Store Failed', [
                'user_id' => $user->id,
                'task_id' => $task->id,
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTraceAsString(),
            ]);
            foreach ($uploadedFiles as $filePath){
                Storage::delete($filePath);
            }
            return response()->json([
                'message' => 'خطایی در ارسال درخواست همکاری رخ داد. لطفاً دوباره تلاش کنید.'
            ], 500);
        }
    }

    public function index(Request $request, Task $task)
    {
        $user = $request->user();
        if ($user->id !== $task->user_id)
        {
            return response()->json(['message'=>'صاحب تسک می تواند درخواست های همکاری را مشاهده کند.'],403);
        }
        $applications = Application::query()->
        select('id',
            'user_id',
            'status',
            'description',
            'task_id',
            'created_at')
            ->where('task_id', $task->id)
            ->where('status','pending')
            ->with(['user:id,full_name'])
            ->latest()
            ->paginate(10);
        return response()->json(['applications' => $applications],200);
    }
    public function show(Request $request, Application $application)
    {
        $user = $request->user();
        if ($user->id !== $application->task->user_id)
        {
            return response()->json(['message'=>'دسترسی دیدن این درخواست همکاری را ندارید'],403);
        }
        $application->load([
            'task:id,user_id',
            'user:id,full_name,avatar',
            'files:id,application_id,file_path',
            'user.skills:id,name',
        ]);
        $application->files->transform(function ($file) {
            $file->download_url = Storage::url($file->file_path);
            return $file;
        });

        return response()->json([
            'application' => $application,
        ], 200);

    }

    public function accept(Request $request, Application $application)
    {
        $user = $request->user();
        $task = $application->task;
        if ($user->id !== $task->user_id){
            return response()->json(['message'=>'دسترسی این عملیات را ندارید.'],403);
        }
        if($task->status !== 'open'){
            return response()->json(['message'=>'این تسک باز نیست و نمیتوان درخواست همکاری روی آن تایید کرد'],409);
        }
        if($application->status !== 'pending'){
            return response()->json(['message'=> 'این درخواست همکاری در حالت انتظار برای تایید نمی باشد.'],409);
        }
        try {
            DB::beginTransaction();
            $task = Task::where('id', $application->task_id)
                ->lockForUpdate()
            ->firstOrFail();
            $application = Application::findOrFail($application->id);
            if($task->status !== 'open'){
                DB::rollBack();
                return response()->json(['message'=>'این تسک باز نیست و نمیتوان درخواست همکاری روی آن تایید کرد'],409);
            }
            if($application->status !== 'pending'){
                DB::rollBack();
                return response()->json(['message'=> 'این درخواست همکاری در حالت انتظار برای تایید نمی باشد.'],409);
            }
            $application->update(['status'=>'accepted']);
            $task->applications()
                ->where('status','pending')
                ->where('id', '!=', $application->id)
                ->update(['status'=>'rejected']);
            $task->update(['status'=>'assigned']);
            $application->project()->create([
                'amount' => $task->budget,
                'deadline' => $task->deadline,
                'started_at' => now(),
            ]);
            DB::commit();
            return response()->json(['message'=>'این درخواست همکاری برای تسک شما انتخاب شد'],200);
        }
        catch (\Exception $exception){
            DB::rollBack();
            Log::error('Accept Application Failed', [
                'user_id' => $user->id,
                'application_id'=> $application->id,
                'task_id' => $task->id,
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTraceAsString(),
            ]);
            return response()->json([
                'message' => 'خطایی در انتخاب درخواست همکاری رخ داد. لطفاً دوباره تلاش کنید.'
            ], 500);
        }
    }
    public function reject(Request $request, Application $application)
    {
        $user = $request->user();
        $task = $application->task;
        if ($user->id !== $task->user_id){
            return response()->json(['message'=>'دسترسی این عملیات را ندارید.'],403);
        }
        if($task->status !== 'open'){
            return response()->json(['message'=>'این تسک باز نیست و نمیتوان درخواست همکاری روی آن رد کرد'],409);
        }
        if($application->status !== 'pending' && $application->status !== 'contacted'){
            return response()->json(['message'=> 'این درخواست همکاری در حالت انتظار برای رد نمی باشد.'],409);
        }
        try {
            DB::beginTransaction();
            $task = Task::where('id', $application->task_id)
                ->lockForUpdate()
                ->firstOrFail();
            $application = Application::where('id', $application->id)
                ->lockForUpdate()
                ->firstOrFail();
            if($task->status !== 'open'){
                DB::rollBack();
                return response()->json(['message'=>'این تسک باز نیست و نمیتوان درخواست همکاری روی آن رد کرد'],409);
            }
            if($application->status !== 'pending' && $application->status !== 'contacted'){
                DB::rollBack();
                return response()->json(['message'=> 'این درخواست همکاری در حالت انتظار برای رد نمی باشد.'],409);
            }
            $application->update(['status'=>'rejected']);
            throw new \Exception('Transaction Test');
            DB::commit();
            return response()->json(['message'=>'این درخواست همکاری با موفقیت رد شد.'],200);
        }
        catch (\Exception $exception){
            DB::rollBack();
            Log::error('reject Application Failed', [
                'user_id' => $user->id,
                'application_id'=> $application->id,
                'task_id' => $task->id,
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTraceAsString(),
            ]);
            return response()->json([
                'message' => 'خطایی در رد کردن درخواست همکاری رخ داد. لطفاً دوباره تلاش کنید.'
            ], 500);
        }
    }
}
