<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $notifications = Notification::query()->select(['id','user_id','title','message','is_read','created_at'])
        ->where('user_id',$user->id)->latest()->paginate(10);
        $unread_count = Notification::query()->where('user_id', $user->id)->where('is_read',false)->count();
        $notifications->getCollection()
            ->where('is_read', false)
            ->each(function ($notification) {
                $notification->update(['is_read' => true]);
            });
        return response()->json(['notifications'=>$notifications
        ,'unread_count'=> $unread_count]);
    }
}
