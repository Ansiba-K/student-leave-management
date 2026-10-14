<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class LeaveAppliedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $leave;
    public $applicant;
    public $applicantType;


    // Receive leave and student information
    public function __construct($leave, $applicant, $applicantType)
    {
        $this->leave = $leave;
        $this->applicant = $applicant;
        $this->applicantType = $applicantType;
    }

    // Build the email
    public function build()
    {
        $subject = $this->applicantType == 1
            ? 'New Student Leave Application'
            : 'New Staff Leave Application';

        return $this->subject($subject)
            ->view('emails.leave_applied');
    }
}
