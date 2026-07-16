<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Notification;
use Illuminate\Support\Facades\DB;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $query = Notification::query();

        // Filter notifications by logged-in user's role
        if (auth()->user()->role === 'admin') {
            $query->where('target_role', 'admin');
        } else {
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
                    if (
                        in_array($notification->type, [
                            'unlock_approved',
                            'unlock_denied'
                        ])
                    ) {
                        $lock = DB::table('odms_admin_quarter_locks')
                            ->find($notification->related_id);
                        if ($lock) {
                            $notification->url = route('accounting.quarterly-summary', [
                                'year'    => $lock->year,
                                'quarter' => $lock->quarter,
                            ]);
                        } else {
                            $notification->url = route('accounting.quarterly-summary');
                        }
                    } else {

                        $notification->url = route('accounting.logbook', [
                            'highlight' => $notification->related_id
                        ]);
                    }
                    break;
                    
                case 'admin':
                    if ($notification->type === 'unlock_request') {
                        $notification->url = route('admin.unlock-requests', [
                            'highlight' => $notification->related_id
                        ]);
                    } else {
                        $notification->url = route('admin.dashboard'); // or wherever other admin notifications go
                    }
                    break;

                default:
                    $notification->url = '#';
                    break;
            }
        }

        return response()->json([
            'unreadCount' => $notifications->where('is_read', 0)->count(),
            'notifications' => $notifications
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

    public function read($id)
    {
        $notification = \App\Models\Notification::findOrFail($id);

        $notification->is_read = 1;
        $notification->save();

        return response()->json([
            'success' => true
        ]);
    }
}
