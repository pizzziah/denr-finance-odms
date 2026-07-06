<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Notification;

class NotificationController extends Controller
{
    public function index()
    {
        return response()->json([
            'unreadCount' => Notification::where('is_read',0)->count(),

            'notifications' => Notification::latest()
                ->take(10)
                ->get()
        ]);
    }

    public function markAsRead($id)
    {
        $notification = Notification::findOrFail($id);

        $notification->update([
            'is_read'=>1
        ]);

        return response()->json([
            'success'=>true
        ]);
    }

    public function readAll()
    {
        Notification::where('is_read',0)
            ->update([
                'is_read'=>1
            ]);

        return response()->json([
            'success'=>true
        ]);
    }
}