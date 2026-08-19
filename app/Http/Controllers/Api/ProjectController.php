<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProjectController extends Controller
{
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
}
