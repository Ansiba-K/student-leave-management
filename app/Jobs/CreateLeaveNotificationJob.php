<?php
namespace App\Jobs;

use App\Services\LeaveNotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CreateLeaveNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $staffId;
    protected $leaveId;
    protected $title;
    protected $message;

    public function __construct($staffId, $leaveId, $title, $message)
    {
        $this->staffId = $staffId;
        $this->leaveId = $leaveId;
        $this->title = $title;
        $this->message = $message;
    }

    public function handle(LeaveNotificationService $leaveNotificationService)
    {
        $leaveNotificationService->createNotification(
            $this->staffId,
            $this->leaveId,
            $this->title,
            $this->message
        );
    }
}
