<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Complaint;
use App\Models\Delivery;
use App\Models\Notification;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\Request;

class UserDashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $activeProject = Project::query()->where(function ($query) use ($user) {
            $query->whereHas('application', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
                ->orWhereHas('application.task', function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                });

        })->where(function ($query) {
            $query->whereIn('status', [
                'in_progress',
                'submitted',
                'revision_requested',
            ])
                ->orWhere(function ($query) {
                    $query->where('status', 'completed')
                        ->where('payment_status', 'unpaid');
                });
        })->count();
        $openTasks = Task::query()->where('user_id', $user->id)->where('status', 'open')->count();

        $sentApplications = Application::query()->where('user_id', $user->id)->where('status', 'pending')->count();

        $recivedApplications = Application::query()->whereHas('task', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->where('status', 'pending')->count();
        $sentDelivery = Delivery::query()->whereHas('project.application', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->where('status', 'pending')->count();
        $recivedDelivery = Delivery::query()->whereHas('project.application.task', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->where('status', 'pending')->count();
        $myCompaints = Complaint::query()->where('user_id', $user->id)->whereIn('status', ['pending', 'reviewing'])->count();
        $relatedComplaints = Complaint::query()
            ->where('user_id', '!=', $user->id)
            ->whereIn('status', ['pending', 'reviewing'])
            ->where(function ($query) use ($user) {
                $query->whereHas('project.application', function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                })
                    ->orWhereHas('project.application.task', function ($query) use ($user) {
                        $query->where('user_id', $user->id);
                    });
            })
            ->count();
        $unreadNotifications = Notification::query()->where('user_id', $user->id)->where('is_read' ,false)->count();
        return response()->json(['activeProject' => $activeProject,
            'openTasks' => $openTasks,
            'sentApplications' => $sentApplications,
            'recivedApplications' => $recivedApplications,
            'sentDelivery' => $sentDelivery,
            'recivedDelivery' => $recivedDelivery,
            'myCompaints' => $myCompaints,
            'relatedComplaints' => $relatedComplaints,
            'unreadNotifications'=> $unreadNotifications], 200);
    }

}
