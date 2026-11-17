<?php

namespace App\Mail;

use App\Models\Order;
use App\Services\Mail\TemplateRenderer;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OrderCompletedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Order $order) {}

    public function build()
    {
        $renderer = app(TemplateRenderer::class);

        $data = [
            'order'          => $this->order,
            'customer_name'  => $this->order->customer_name ?? $this->order->billing_name ?? 'Customer',
        ];

        $tpl = $renderer->render('order_completed', $data);

        return $this->subject($tpl['subject'])
                    ->html($tpl['html']);
    }
}
