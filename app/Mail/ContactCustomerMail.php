<?php
namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ContactCustomerMail extends Mailable
{
    use Queueable, SerializesModels;

    public array $data;

    public function __construct(array $data) { $this->data = $data; }

    public function build()
    {
        return $this->subject($this->data['subject'] ? ('We received: '.$this->data['subject']) : 'We received your request')
                    ->markdown('emails.contact.customer', ['data' => $this->data]);
    }
}
