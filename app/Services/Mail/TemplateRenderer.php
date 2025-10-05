<?php

namespace App\Services\Mail;

use App\Models\EmailTemplate;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Str;

class TemplateRenderer
{
    /**
     * Ritorna array ['subject' => ..., 'html' => ...]
     * Se non trova o disabilitato → usa la view fallback.
     */
    public function render(string $key, array $data = []): array
    {
        $tpl = EmailTemplate::where('key', $key)->where('enabled', true)->first();

        if ($tpl) {
            $subject = Blade::render($tpl->subject, $data);
            $html    = Blade::render($tpl->body_html, $data);
            return ['subject' => trim($subject), 'html' => $html];
        }

        // fallback
        $subject = 'Order completed #'.data_get($data, 'order.number', Str::random(6));
        $html    = view('emails.order_completed_default', $data)->render();

        return ['subject' => $subject, 'html' => $html];
    }
}