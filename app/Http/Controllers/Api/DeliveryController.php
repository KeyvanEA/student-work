<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Delivery\RejectDeliveryRequest;
use App\Http\Requests\Delivery\StoreDeliveryRequest;
use App\Models\Delivery;
use App\Models\Project;
use http\Env\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class DeliveryController extends Controller
{
    public function store(Project $project,StoreDeliveryRequest $storeDelivery)
    {
        $user = $storeDelivery->user();
        $uploadedFiles = [];

        if ($user->id !== $project->application->user_id) {
            return response()->json(['message' => 'شما دسترسی انجام این عملیات را ندارید.'], 403);
        }
        if (now() > $project->deadline){
            return response()->json(['message' => 'زمان تحویل پروژه به پایان رسیده است'],422);
        }
        if ($project->status !== 'in_progress' && $project->status !== 'revision_requested') {
            return response()->json(['message' => 'شما قادر به تحویل نیستید پروژه بسته می باشد.'],422);
        }
        $lastDelivery = $project->deliveries()->latest()->first();
        if ($lastDelivery && (
                $lastDelivery->status === 'accepted' ||
                $lastDelivery->status === 'pending'
            )) {
            return response()->json(['message' => 'شما یک تحویل قبول شده یا در انتظار بررسی دارید قادر به تحویل جدید نمی باشید.'],422);
        }
        $validatedData = $storeDelivery->validated();
        try {
            DB::beginTransaction();
            $project->status = 'submitted';
            $project->save();
            $deliveryData = Arr::only($validatedData,['description']);
            $deliveryData['project_id'] = $project->id;
            $deliveryData['status'] = 'pending';
            $deliveryData['submitted_at'] = now();
            $deliveryData['edit_count'] = 0;
            $delivery = $project->deliveries()->create($deliveryData);

            if (!empty($validatedData['files'])) {
                foreach ($validatedData['files'] as $file) {
                    $filePath =  $file->store("deliveries/{$delivery->id}/attachments");
                    $uploadedFiles[] = $filePath;
//                    throw new \Exception('Transaction Test');
                    $delivery->files()->create([
                        'file_path' => $filePath,
                        'original_name' => $file->getClientOriginalName(),
                        'mime_type' => $file->getClientMimeType(),
                        'size' => $file->getSize(),
                    ]);
                }
            }
            DB::commit();
            return response()->json(['message'=>'شما پروژه خود را با موفقیت نحویل دادید. منتظر بررسی کارفرما بمانید.'], 201);
        }
        catch (\Exception $exception){
            DB::rollBack();
            Log::error('Delivery Store Failed', [
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
                'message' => 'خطایی در تحویل پروژه شما رخ داد. لطفاً دوباره تلاش کنید.'
            ], 500);
        }

    }
    public function reject(Delivery $delivery , RejectDeliveryRequest $request)
    {
        $user = $request->user();
        $taskOwner = $delivery->project->application->task->user_id;
        $project = $delivery->project;
        if ($taskOwner !== $user->id) {
            return response()->json(['message'=>'شما دسترسی این عملیات را ندارید.'],403);
        }
        if ($delivery->status !== 'pending') {
            return response()->json(['message'=> 'تحویل پروژه در حالت انتظار نمی باشد.'],422);
        }
        if ($project->status !== 'submitted') {
            return response()->json(['message' => 'تحویلی برای این پروژه ثبت نشده است.'],422);
        }
        $rejectedCount = $project->deliveries()
            ->where('status', 'rejected')
            ->count();
        if ($rejectedCount >= 3) {
            return response()->json(['message' => 'شما تعداد مجاز رد کردن این تحویل پروژه را گذرانده اید. در صورت عدم برآورده شدن نیاز تسک، برای ادمین شکایت ثبت کنید'],422);
        }
        try {
            DB::beginTransaction();
            $delivery->status = 'rejected';
            $delivery->rejection_reason = $request->validated('rejection_reason');
            $delivery->save();
            $project->status = 'revision_requested';
            $project->save();
            DB::commit();
            return response()->json(['message'=>'شما این تحویل را با موفقیت رد کردید. منتظر تحویل بعدی باشید.'], 200);
        }
        catch (\Exception $exception){
            DB::rollBack();
            Log::error('Reject Delivery Failed', [
                'user_id' => $user->id,
                'delivery_id' => $delivery->id,
                'project_id' => $project->id,
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTraceAsString(),
            ]);
            return response()->json([
                'message' => 'خطایی در رد کردن این تحویل رخ داد. لطفاً دوباره تلاش کنید.'
            ], 500);
        }
    }
    public function show(Request $request, Delivery $delivery)
    {
        $user = $request->user();
        $application = $delivery->project->application;
        $task = $application->task;
        if ($user->id !== $task->user_id && $user->id !== $application->user_id) {
            return response()->json([
                'message' => 'شما دسترسی لازم برای مشاهده این تحویل پروژه را ندارید'
            ], 403);
        }
        $delivery->load('files');
        return response()->json([
            'delivery' => [
                'id' => $delivery->id,
                'description' => $delivery->description,
                'status' => $delivery->status,
                'submitted_at' => $delivery->submitted_at,
                'edit_count' => $delivery->edit_count,
                'files' => $delivery->files->map(function ($file) {
                    return [
                        'id' => $file->id,
                        'original_name' => $file->original_name,
                        'mime_type' => $file->mime_type,
                        'size' => $file->size,
                        'preview_url' => '',
            ];
                }),
            ],
        ]);

    }
}
