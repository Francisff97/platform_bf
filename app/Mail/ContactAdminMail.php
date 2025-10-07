<?php
namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ContactAdminMail extends Mailable
{
    use Queueable, SerializesModels;

    public array $data;

    public function __construct(array $data) { $this->data = $data; }

    public function build()
    {
        return $this->subject($this->data['subject'] ? ('New contact: '.$this->data['subject']) : 'New contact request')
                    ->markdown('emails.contact.admin', ['data' => $this->data]);
    }
}
