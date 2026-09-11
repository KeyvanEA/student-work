<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreComplaintRequest;
use App\Models\Complaint;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ComplaintController extends Controller
{
    public function store(StoreComplaintRequest $request, Project $project)
    {
        $user = $request->user();
        $uploadedFiles = [];

        if ($user->id !== $project->application->user_id && $user->id !== $project->application->task->user_id) {
            return response()->json(['message' => 'شما دسترسی این عملیات را ندارید'], 403);
        }

        if ($user->id === $project->application->user_id && $project->status !== 'revision_requested') {
            return response()->json(['message' => 'پروژه در وضعیتی برای ثبت شکایت نمی باشد.'],422);
        }
        if ($user->id === $project->application->task->user_id && $project->status !== 'submitted') {
            return response()->json(['message' => 'پروژه در وضعیتی برای ثبت شکایت نمی باشد.'],422);
        }

        try {
            DB::beginTransaction();
            $project = Project::where('id', $project->id)
                ->lockForUpdate()
                ->firstOrFail();
            if ($user->id === $project->application->user_id && $project->status !== 'revision_requested') {
                DB::rollBack();
                return response()->json(['message' => 'پروژه در وضعیتی برای ثبت شکایت نمی باشد.'],422);
            }
            if ($user->id === $project->application->task->user_id && $project->status !== 'submitted') {
                DB::rollBack();
                return response()->json(['message' => 'پروژه در وضعیتی برای ثبت شکایت نمی باشد.'],422);
            }
            $lastComplaint = $project->complaints()
                ->where('user_id', $user->id)
                ->latest()
                ->first();
            if ($lastComplaint && (
                    $lastComplaint->status === 'reviewing' ||
                    $lastComplaint->status === 'pending'
                )) {
                DB::rollBack();
                return response()->json(['message' => 'شما یک شکایت در انتظار بررسی دارید قادر به ثبت شکایت جدید نمی باشید.'],422);
            }
            $project->status = 'disputed';
            $project->save();
            $validatedData = $request->validated();
            $complaintData = Arr::only($validatedData,['title','description']);
            $complaintData['user_id'] = $user->id;
            $complaintData['project_id'] = $project->id;
            $complaintData['status'] = 'pending';
            $complaint = $project->complaints()->create($complaintData);
            if (!empty($validatedData['files'])) {
                foreach ($validatedData['files'] as $file) {
                    $filePath =  $file->store("complaints/{$complaint->id}/attachments");
                    $uploadedFiles[] = $filePath;
                    $complaint->files()->create([
                        'file_path' => $filePath,
                        'original_name' => $file->getClientOriginalName(),
                        'mime_type' => $file->getClientMimeType(),
                        'size' => $file->getSize(),
                    ]);
                }
            }

            DB::commit();
            return response()->json(['message'=>'شکایت شما با موفقیت ثبت شد منتظر بررسی و پاسخ ادمین بمانید.'], 201);
        }
        catch (\Exception $exception) {
            DB::rollBack();
            Log::error('Complaint Store Failed', [
                'user_id' => $user->id,
                'project_id' => $project->id,
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTraceAsString(),
            ]);
            foreach ($uploadedFiles as $filePath){
                Storage::delete($filePath);
            }
            return response()->json([
                'message' => 'خطایی در ثبت شکایت رخ داد. لطفاً دوباره تلاش کنید.'
            ], 500);

        }
    }
    public function index(Request $request)
    {
        $user = $request->user();

        $complaints = Complaint::query()
            ->select(['id', 'project_id', 'title', 'status', 'created_at'])
            ->where('user_id', $user->id)
            ->latest()
            ->paginate(10);
        return response()->json([
            'complaints' => $complaints,
        ], 200);

    }
    public function show(Request $request, Complaint $complaint)
    {
        $user = $request->user();
        if ($user->id !== $complaint->user_id)
        {
            return response()->json(['message' => 'شما دسترسی این عملیات را ندارید'],403);
        }
        $complaint->load([
            'files:id,complaint_id,original_name,mime_type,size,file_path',
        ]);
        $complaint->files->transform(function ($file) {
            $file->download_url = Storage::url($file->file_path);
            return $file;
        });

        return response()->json([
            'complaint' => $complaint,
        ], 200);
    }
    public function related(Request $request)
    {
        $user = $request->user();

        $complaints = Complaint::query()
            ->select([
                'id',
                'project_id',
                'title',
                'status',
                'created_at',
            ])
            ->where('user_id', '!=', $user->id)
            ->whereHas('project.application', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->orWhere(function ($query) use ($user) {
                $query->where('user_id', '!=', $user->id)
                    ->whereHas('project.application.task', function ($query) use ($user) {
                        $query->where('user_id', $user->id);
                    });
            })
            ->latest()
            ->paginate(10);

        return response()->json([
            'complaints' => $complaints,
        ], 200);
    }
}
