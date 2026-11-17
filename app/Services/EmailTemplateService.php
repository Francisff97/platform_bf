<?php

namespace App\Services;

use App\Models\EmailTemplate;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Mail;

class EmailTemplateService
{
    /**
     * Renderizza un template per chiave usando dati $data.
     * Ritorna array ['subject' => ..., 'html' => ...]
     */
    public function render(string $key, array $data = []): array
    {
        /** @var EmailTemplate|null $tpl */
        $tpl = EmailTemplate::where('key', $key)->first();

        // fallback minimale
        if (!$tpl || !$tpl->enabled) {
            return [
                'subject' => $data['subject'] ?? ucfirst(str_replace('_',' ', $key)),
                'html'    => $data['text']    ?? '<p>No template found.</p>',
            ];
        }

        // subject
        $subject = Blade::render($tpl->subject ?? '', $data);

        // body_html
        $html = Blade::render($tpl->body_html ?? '', $data);

        return ['subject' => $subject, 'html' => $html];
    }

    /**
     * Render + invio veloce.
     */
    public function send(string $key, string $to, array $data = []): void
    {
        $r = $this->render($key, $data);

        Mail::html($r['html'], function ($m) use ($to, $r) {
            $m->to($to)->subject($r['subject']);
        });
    }
}