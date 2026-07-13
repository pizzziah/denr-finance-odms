<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Notification;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $query = Notification::query();

        // Admin sees all notifications
        if (auth()->user()->role != 'admin') {
            $query->where('target_role', auth()->user()->role);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $notifications = $query
            ->latest()
            ->take(20)
            ->get();

        foreach ($notifications as $notification) {

            switch ($notification->target_role) {

                case 'budget':
                    $notification->url = route(
                        'budget.logbook',
                        [
                            'highlight' => $notification->related_id
                        ]
                    );
                    break;

                case 'accountant':
                    $notification->url = route(
                        'accounting.logbook',
                        [
                            'highlight' => $notification->related_id
                        ]
                    );
                    break;

                case 'admin':
                    $notification->url = route(
                        'admin.unlock-requests',
                        [
                            'highlight' => $notification->related_id
                        ]
                    );
                    break;

                default:
                    $notification->url = '#';
            }
        }

        return response()->json([
            'unreadCount' => $notifications->where('is_read', 0)->count(),
            'notifications' => $notifications,
        ]);
    }

    public function markAsRead($id)
    {
        $notification = Notification::findOrFail($id);

        $notification->update([
            'is_read' => 1,
        ]);

        return response()->json([
            'success' => true,
        ]);
    }

    public function readAll()
    {
        Notification::where('is_read', 0)
            ->update([
                'is_read' => 1,
            ]);

        return response()->json([
            'success' => true,
        ]);
    }
}
