<?php

namespace App\Listeners;

use App\Events\LeaveApplied;
use Illuminate\Support\Facades\Log;

class LogLeaveApplication
{
    public function handle(LeaveApplied $event)
    {
        Log::info('Leave application event triggered', [
            'leave_id' => $event->leaveId,
            'applicant_type' => $event->applicantType,
        ]);
    }
}
