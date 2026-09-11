<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Notification;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        $role = $request->query('role');
        $status = $request->query('status');
        $user = $request->user();
        $projects = Project::query();
        if ($role === 'worker') {
            $projects->whereHas('application', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            });
        }
        if ($role === 'employer') {
            $projects->whereHas('application.task', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            });
        }
        if ($status === 'active') {
            $projects->where(function ($query) {
                $query->whereIn('status', [
                    'in_progress',
                    'submitted',
                    'revision_requested',
                ])
                    ->orWhere(function ($query) {
                        $query->where('status', 'completed')
                            ->where('payment_status', 'unpaid');
                    });
            });
        }
        if ($status === 'history') {
            $projects->where(function ($query) {
                $query->where('status', 'cancelled')
                    ->orWhere(function ($query) {
                        $query->where('status', 'completed')
                            ->where('payment_status', 'paid');
                    });
            });
        }
        $projects = $projects->with([ 'application.user:id,full_name,mobile,avatar',
            'application.task.user:id,full_name,mobile,avatar',])->latest()->paginate(10);
        $projects->getCollection()->transform(function ($project) use ($user) {

            if ($user->id === $project->application->user_id) {
                $role = 'worker';
                $otherUser = $project->application->task->user;
            } else {
                $role = 'employer';
                $otherUser = $project->application->user;
            }

            return [
                'id' => $project->id,
                'title' => $project->application->task->title,
                'amount' => $project->amount,
                'deadline' => $project->deadline,
                'status' => $project->status,
                'payment_status' => $project->payment_status,
                'role' => $role,
                'other_user' => [
                    'id' => $otherUser->id,
                    'full_name' => $otherUser->full_name ?: $otherUser->mobile,
                    'avatar' => $otherUser->avatar,
                ],
            ];
        });
        return response()->json(['projects' => $projects],200);
    }
    public function show(Project $project , Request $request)
    {
        $user = $request->user();
        $application = $project->application;
        $task = $application->task;
        if ($user->id !== $task->user_id && $user->id !== $application->user_id) {
            return response()->json([
                'message' => 'شما دسترسی لازم برای مشاهده این پروژه را ندارید'
            ], 403);
        }
        $project->load([
            'application:id,description,user_id,task_id',
            'application.user:id,full_name,avatar',
            'application.task:id,title,description,user_id,category_id',
            'application.task.user:id,full_name,avatar',
            'application.task.category:id,name',
            'application.task.files:id,task_id,file_path',
        ]);
        $project->application->task->files->transform(function ($file) {
            $file->download_url = Storage::url($file->file_path);

            return $file;
        });

        return response()->json(['project' => $project],200);
    }
    public function payment(Project $project, Request $request)
    {
        $user = $request->user();
        $task = $project->application->task;
        if ($user->id !== $task->user_id) {
            return response()->json(['message' => 'شما دسترسی این عملیات را ندارید'],403);
        }
       if ($project->status !== 'completed') {
           return response()->json(['message' => 'این پروژه تکمیل نشده است.'],422);
       }
       if ($project->payment_status === 'paid') {
           return response()->json(['message' => 'شما یک بار دستمزد این پروژه را پرداخت کرده اید'],409);
       }
        try {
            DB::beginTransaction();
            $project = Project::where('id', $project->id)
                ->lockForUpdate()
                ->firstOrFail();
            if ($project->status !== 'completed') {
                DB::rollBack();
                return response()->json(['message' => 'این پروژه تکمیل نشده است.'],422);
            }
            if ($project->payment_status === 'paid') {
                DB::rollBack();
                return response()->json(['message' => 'شما یک بار دستمزد این پروژه را پرداخت کرده اید'],409);
            }
            $project->payment_status = 'paid';
            $project->save();
            DB::commit();
            Notification::create([
                'user_id' => $project->application->user_id,
                'title' => 'پرداخت دستمزد',
                'message' => 'توسط کارفرما پرداخت شد.'.$project->application->task->title.'دستمزد شما برای',
                'is_read' => false,
            ]);
            return response()->json(['message' => 'پرداخت دستمزد پروژه با موفقیت ثبت شد.'],200);
        }
       catch (\Exception $exception) {
           DB::rollBack();
           Log::error('payment Failed', [
               'user_id' => $user->id,
               'project_id' => $project->id,
               'message' => $exception->getMessage(),
               'file' => $exception->getFile(),
               'line' => $exception->getLine(),
               'trace' => $exception->getTraceAsString(),
           ]);
           return response()->json([
               'message' => 'خطایی در پرداخت دستمزد رخ داد. لطفاً دوباره تلاش کنید.'
           ], 500);
       }
    }

}
