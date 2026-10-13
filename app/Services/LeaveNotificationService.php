<?php
namespace App\Services;

use Illuminate\Support\Facades\DB;

class LeaveNotificationService
{
    public function createNotification($staffId, $leaveId, $title, $message)
    {
        return DB::table('leave_notifications')->insertGetId([
            'staff_id' => $staffId,
            'leave_id' => $leaveId,
            'title' => $title,
            'message' => $message,
            'is_read' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}