<?php

namespace Modules\Advisor\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AdvisorSupportMessage extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public $title;
    public $messageBody;
    public $advisorName;
    public function __construct($title,$message,$advisorName)
    {
        $this->title = $title;
        $this->messageBody = $message;
        $this->advisorName = $advisorName;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('advisor::email.advisor-message');
    }
}
