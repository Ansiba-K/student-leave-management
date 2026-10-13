<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LeaveNotificationController extends Controller
{
    public function index($staffId)
    {
        // Check whether the staff member exists
        $staff = DB::table('staff')
            ->where('id', $staffId)
            ->first();

        if (!$staff) {
            return response()->json([
                'success' => false,
                'message' => 'Staff not found',
                'data' => null,
            ], 404);
        }

        // Get notifications for this staff member
        $notifications = DB::table('leave_notifications')
            ->where('staff_id', $staffId)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Notifications retrieved successfully',
            'data' => $notifications,
        ], 200);
    }


    public function markAsRead($staffId, $notificationId)
    {
        $staff = DB::table('staff')
            ->where('id', $staffId)
            ->first();

        if (!$staff) {
            return response()->json([
                'success' => false,
                'message' => 'Staff not found',
                'data' => null,
            ], 404);
        }

        $notification = DB::table('leave_notifications')
            ->where('id', $notificationId)
            ->where('staff_id', $staffId)
            ->first();

        if (!$notification) {
            return response()->json([
                'success' => false,
                'message' => 'Notification not found for this staff',
                'data' => null,
            ], 404);
        }

        if ($notification->is_read == 1) {
            return response()->json([
                'success' => true,
                'message' => 'Notification is already read',
                'data' => $notification,
            ], 200);
        }

        DB::table('leave_notifications')
            ->where('id', $notificationId)
            ->where('staff_id', $staffId)
            ->update([
                'is_read' => 1,
                'updated_at' => now(),
            ]);

        $notification = DB::table('leave_notifications')
            ->where('id', $notificationId)
            ->first();

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read',
            'data' => $notification,
        ], 200);
    }
}
