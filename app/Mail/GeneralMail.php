<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use General;

class GeneralMail extends Mailable
{
    use Queueable, SerializesModels;
    public $email;
    public $settings;
    public $appname;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($email){
        $this->email = $email;
        $this->appname = env("APP_HOME");
        $general = new General();
        $this->settings = $general->settings();
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build() {
        return $this->view('mails.GeneralMail')
                ->subject($this->email->subject);
    }
}
