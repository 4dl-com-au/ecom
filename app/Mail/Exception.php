<?php
namespace App\Mail;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
class Exception extends Mailable
{
    use Queueable, SerializesModels;
    public $data;
    /**
     * Create a new message instance.
     *
     * @param $data
     */
    public function __construct($data)
    {
        $this->data = $data;
    }
    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('mails.exception')
            ->subject('Exception Logged!')
            ->from(env('MAIL_FROM_ADDRESS'))
            ->to('crash@getecom.net')
            ->with([
                'data' => $this->data,
            ]);
    }
}