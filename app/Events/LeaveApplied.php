<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LeaveApplied
{
    use Dispatchable, SerializesModels;

    public $leaveId;
    public $applicantType;

    public function __construct($leaveId, $applicantType)
    {
        $this->leaveId = $leaveId;
        $this->applicantType = $applicantType;
    }
}
