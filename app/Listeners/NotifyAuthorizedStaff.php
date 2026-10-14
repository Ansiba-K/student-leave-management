<?php

namespace App\Listeners;

use App\Events\LeaveApplied;
use App\Jobs\CreateLeaveNotificationJob;
use Illuminate\Support\Facades\DB;

class NotifyAuthorizedStaff
{
    public function handle(LeaveApplied $event)
    {
        // Find the newly applied leave
        $leave = DB::table('leaves')
            ->where('id', $event->leaveId)
            ->first();

        if (!$leave) {
            return;
        }

        // Find the applicant's department
        if ($leave->applicant_type == 1) {
            $applicant = DB::table('students')
                ->where('id', $leave->student_id)
                ->first();
        } else {
            $applicant = DB::table('staff')
                ->where('id', $leave->staff_id)
                ->first();
        }

        if (!$applicant || !$applicant->department_id) {
            return;
        }

        $departmentId = $applicant->department_id;

        // Get HOD and authorized staff from the same department
        $departmentAuthorities = DB::table('staff')
            ->where('department_id', $departmentId)
            ->where('is_authority', 1)
            ->whereIn('role', [1, 2])
            ->pluck('id');

        // Get authorized principals from every department
        $principals = DB::table('staff')
            ->where('role', 3)
            ->where('is_authority', 1)
            ->pluck('id');

        // Combine recipients and remove duplicate IDs
        $recipients = $departmentAuthorities
            ->merge($principals)
            ->unique();

        // Do not notify a staff member about their own leave
        if ($leave->applicant_type == 2) {
            $recipients = $recipients->reject(function ($id) use ($leave) {
                return $id == $leave->staff_id;
            });
        }

        // Create a queued notification for each recipient
        foreach ($recipients as $staffId) {
            CreateLeaveNotificationJob::dispatch(
                $staffId,
                $leave->id,
                'New Leave Application',
                'A new leave application has been submitted and is awaiting review.'
            );
        }
    }
}
