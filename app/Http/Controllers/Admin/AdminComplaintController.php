<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ResolveComplaintRequest;
use App\Models\Complaint;
use App\Models\Notification;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AdminComplaintController extends Controller
{
    public function index(Request $request){

        $status = $request->query('status');
        if (!in_array($status, ['pending', 'reviewing', 'resolved', 'rejected',null],true)) {
            return response()->json(['message' => 'نوع عملیات نامعتبر است.'],422);
        }
        $complaints = Complaint::query()
            ->select([
                'id',
                'project_id',
                'user_id',
                'title',
                'status',
                'created_at',
            ])
            ->with([
                'user:id,full_name,avatar',
                'project:id,application_id,amount',
                'project.application.task:id,title',
            ]);
        if ($status !== null) {
            $complaints->where('status', $status);
        }
        $complaints = $complaints->latest()->paginate(10);
        return response()->json(['complaints' => $complaints], 200);
    }


    public function show(Complaint $complaint, Request $request)
    {
        $user = $request->user();

        if (!$user->hasRole('admin')) {
            return response()->json([
                'message' => 'شما دسترسی این عملیات را ندارید'
            ], 403);
        }

        $complaint->load([
            'user:id,full_name,avatar,mobile,student_number,field_of_study,university_name',
            'files',
            'project.application.user:id,full_name,avatar,mobile,student_number,field_of_study,university_name',
            'project.application.task.user:id,full_name,avatar,mobile,student_number,field_of_study,university_name',
            'project.application.task.files',
            'project.application.files',
            'project.deliveries.files',
        ]);

        $project = $complaint->project;
        $application = $project->application;
        $task = $application->task;

        if ($complaint->user_id === $application->user_id) {
            $otherParty = $task->user;
        } else {
            $otherParty = $application->user;
        }

        return response()->json([
            'complaint' => [
                'id' => $complaint->id,
                'title' => $complaint->title,
                'description' => $complaint->description,
                'status' => $complaint->status,
                'admin_response' => $complaint->admin_response,
                'created_at' => $complaint->created_at,
            ],

            'complainant' => $complaint->user,

            'other_party' => $otherParty,

            'task' => $task,

            'application' => $application,

            'project' => $project,

            'deliveries' => $project->deliveries,

            'complaint_files' => $complaint->files,
        ], 200);
    }
    public function review(request $request, Complaint $complaint)
    {
        $user = $request->user();
        try {
            DB::beginTransaction();
            $complaint = Complaint::where('id', $complaint->id)
                ->lockForUpdate()
                ->firstOrFail();
            if ($complaint->status !== 'pending') {
                DB::rollBack();
                return response()->json(['message'=>'این شکایت در حالت انتظار نمی باشد'],422);
            }
            if ($complaint->project->status !== 'disputed') {
                DB::rollBack();
                return response()->json(['message' => 'پروژه از وضعیت  شکایت شده خارج شده است. '],422);
            }
            $complaint->status = 'reviewing';
            $complaint->save();
            DB::commit();
            return response()->json(['message' => 'شکایت مورد نظر به وضعیت در حال بررسی تغییر پیدا کرد.',
                'complaint' => ['id'=>$complaint->id,'status' => $complaint->status]
            ], 200);
        }
        catch (\Exception $exception){
            DB::rollBack();
            Log::error('complaint change status to reviewing Failed', [
                'user_id' => $user->id,
                'complaint'=> $complaint->id,
                'project_id' => $complaint->project_id,
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTraceAsString(),
            ]);
            return response()->json([
                'message' => 'خطایی در تغییر وضعیت شکایت رخ داد. لطفاً دوباره تلاش کنید.'
            ], 500);
        }
    }
    public function resolve(ResolveComplaintRequest $request, Complaint $complaint)
    {
        $user = $request->user();

        if ($complaint->status !== 'reviewing') {
            return response()->json([
                'message' => 'شکایت در حالت بررسی نمی‌باشد'
            ], 422);
        }

        try {
            DB::beginTransaction();

            $complaint = Complaint::where('id', $complaint->id)
                ->lockForUpdate()
                ->firstOrFail();

            $project = Project::where('id', $complaint->project_id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($complaint->status !== 'reviewing') {
                DB::rollBack();

                return response()->json([
                    'message' => 'شکایت در حالت بررسی نمی‌باشد'
                ], 422);
            }

            if ($project->status !== 'disputed') {
                DB::rollBack();

                return response()->json([
                    'message' => 'پروژه از وضعیت شکایت شده خارج شده است'
                ], 422);
            }

            $validated = $request->validated();

            $application = $project->application;
            $task = $application->task;

            $isWorkerComplaint = $complaint->user_id === $application->user_id;

            if ($validated['decision'] === 'invalid') {

                $complaint->status = 'rejected';
                $complaint->admin_response = $validated['admin_response'];
                $complaint->save();

                $project->status = $isWorkerComplaint
                    ? 'revision_requested'
                    : 'submitted';

                $project->save();

                $complainantMessage =
                    'شکایت شما توسط مدیریت بررسی شد و نامعتبر تشخیص داده شد. پروژه به روند قبلی خود بازگشت.';

                $otherPartyMessage =
                    'شکایت ثبت‌شده درباره این پروژه توسط مدیریت بررسی شد و نامعتبر تشخیص داده شد. پروژه به روند قبلی خود بازگشت.';
            } else {

                if ($validated['action'] === 'cancel') {

                    $complaint->status = 'resolved';
                    $complaint->admin_response = $validated['admin_response'];
                    $complaint->save();

                    $project->status = 'cancelled';
                    $project->save();

                    $complainantMessage =
                        'شکایت شما توسط مدیریت بررسی شد و معتبر تشخیص داده شد. پروژه توسط مدیریت لغو شد.';

                    $otherPartyMessage =
                        'شکایت ثبت‌شده درباره این پروژه توسط مدیریت بررسی شد و معتبر تشخیص داده شد. پروژه توسط مدیریت لغو شد.';
                } else {

                    $complaint->status = 'resolved';
                    $complaint->admin_response = $validated['admin_response'];
                    $complaint->save();

                    $project->status = $isWorkerComplaint
                        ? 'submitted'
                        : 'revision_requested';

                    $project->save();

                    $complainantMessage =
                        'شکایت شما توسط مدیریت بررسی شد و معتبر تشخیص داده شد. پروژه برای ادامه فرآیند وارد مرحله بعدی شد.';

                    $otherPartyMessage =
                        'شکایت ثبت‌شده درباره این پروژه توسط مدیریت بررسی شد و معتبر تشخیص داده شد. پروژه برای ادامه فرآیند وارد مرحله بعدی شد.';
                }
            }

            DB::commit();

        } catch (\Exception $exception) {

            DB::rollBack();

            Log::error('complaint resolve failed', [
                'user_id' => $user->id,
                'complaint_id' => $complaint->id,
                'project_id' => $complaint->project_id,
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
            ]);

            return response()->json([
                'message' => 'خطایی در ثبت نتیجه شکایت رخ داد. لطفاً دوباره تلاش کنید.'
            ], 500);
        }

        try {

            Notification::create([
                'user_id' => $complaint->user_id,
                'title' => 'نتیجه بررسی شکایت',
                'message' => $complainantMessage .
                    ' توضیح مدیریت: ' . $validated['admin_response'],
                'is_read' => false,
            ]);

            $otherPartyId = $isWorkerComplaint
                ? $task->user_id
                : $application->user_id;

            Notification::create([
                'user_id' => $otherPartyId,
                'title' => 'نتیجه بررسی شکایت',
                'message' => $otherPartyMessage .
                    ' توضیح مدیریت: ' . $validated['admin_response'],
                'is_read' => false,
            ]);

        } catch (\Exception $exception) {

            Log::error('complaint notification creation failed', [
                'user_id' => $user->id,
                'complaint_id' => $complaint->id,
                'project_id' => $complaint->project_id,
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
            ]);
        }

        return response()->json([
            'message' => 'نتیجه شکایت کاربر مورد نظر با موفقیت ثبت شد.'
        ], 200);
    }
}

