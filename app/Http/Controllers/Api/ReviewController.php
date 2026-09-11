<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreReviewRequest;
use App\Models\Project;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReviewController extends Controller
{
    public function store(StoreReviewRequest $request,Project $project)
    {
        $user = $request->user();
        $application = $project->application;
        $task = $application->task;
        if ($user->id !== $application->user_id && $user->id !== $task->user_id) {
            return response(['message' => 'شما دسترسی به این عملیات را ندارید.'], 403);
        }
        if ($project->status !== 'completed' || $project->payment_status !== 'paid') {
            return response()->json(['message' => 'همکاری شما به پایان نرسیده است'],422);
        }
        try {
            DB::beginTransaction();
            $project = Project::where('id', $project->id)
                ->lockForUpdate()
                ->firstOrFail();
            $application = $project->application;
            $task = $application->task;
            if ($project->status !== 'completed' || $project->payment_status !== 'paid') {
                DB::rollBack();
                return response()->json(['message' => 'همکاری شما به پایان نرسیده است'],422);
            }
            $lastReview = $project->reviews()
                ->where('reviewer_id', $user->id)
                ->exists();
            if ($lastReview) {
                DB::rollBack();
                return response()->json(['message' => 'شما یکبار رضایت خود را ثبت کرده اید'],409);
            }
            if ($user->id === $task->user_id) {
                $reviewed_user_id = $application->user_id;
            } else {
                $reviewed_user_id = $task->user_id;
            }

            $validatedData = $request->validated();
            $review = $project->reviews()->create(['project_id' => $project->id,
                'reviewer_id' => $user->id,
                'reviewed_user_id' => $reviewed_user_id ,
                'is_satisfied' => $validatedData['is_satisfied']]);
            DB::commit();
            return response()->json(['review' => $review,'message'=>'رضایت شما از همکاری با موفقیت ثبت شد.'],201);
        }
        catch (\Exception $exception) {
            DB::rollBack();
            Log::error('Review Store Failed', [
                'user_id' => $user->id,
                'project_id' => $project->id,
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTraceAsString(),
            ]);
            return response()->json(['message' => 'ثبت رضایت شما موفقیت آمیز نبود. دوباره رضایت خود را ثبت کنید'],500);
        }
    }
    public function satisfaction(Request $request)
    {
        $user = $request->user();

        $workerSatisfied = Review::query()
            ->where('reviewed_user_id', $user->id)
            ->where('is_satisfied', true)
            ->whereHas('project.application', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->count();

        $workerDissatisfied = Review::query()
            ->where('reviewed_user_id', $user->id)
            ->where('is_satisfied', false)
            ->whereHas('project.application', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->count();

        $employerSatisfied = Review::query()
            ->where('reviewed_user_id', $user->id)
            ->where('is_satisfied', true)
            ->whereHas('project.application.task', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->count();

        $employerDissatisfied = Review::query()
            ->where('reviewed_user_id', $user->id)
            ->where('is_satisfied', false)
            ->whereHas('project.application.task', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->count();

        return response()->json([
            'as_worker' => [
                'satisfied_count' => $workerSatisfied,
                'dissatisfied_count' => $workerDissatisfied,
            ],
            'as_employer' => [
                'satisfied_count' => $employerSatisfied,
                'dissatisfied_count' => $employerDissatisfied,
            ],
        ], 200);
    }
}
