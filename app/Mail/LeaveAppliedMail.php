<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class LeaveAppliedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $leave;
    public $student;

    // Receive leave and student information
    public function __construct($leave, $student)
    {
        $this->leave = $leave;
        $this->student = $student;
    }

    // Build the email
    public function build()
    {
        return $this->subject('New Student Leave Application')
                    ->view('emails.leave_applied');
    }
}
