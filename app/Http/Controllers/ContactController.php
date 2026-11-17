<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class ContactController extends Controller
{
    public function submit(Request $r)
    {
        // Honeypot: se "company" è pieno → fingo successo (bot)
        if (filled($r->input('company'))) {
            return back()->with('success', 'Thanks! We will get back to you soon.');
        }

        // Validazione base
        $data = $r->validate([
            'name'    => 'required|string|max:120',
            'email'   => 'required|email|max:160',
            'subject' => 'nullable|string|max:160',
            'message' => 'required|string|min:10|max:5000',
            'privacy' => 'accepted',
            'g-recaptcha-response' => 'nullable|string',
        ]);

        // reCAPTCHA v3 (opzionale)
        $secret = $this->recaptchaSecret();
        $token  = (string) $r->input('g-recaptcha-response', '');
        if ($secret && $token !== '') {
            if (!$this->verifyRecaptcha($secret, $token, $r->ip())) {
                return back()->withInput()
                    ->with('error', 'reCAPTCHA validation failed. Please try again.');
            }
        }

        // Destinatario (fallback sicuro)
        $to   = config('mail.from.address') ?: 'support@'.parse_url(config('app.url', 'http://localhost'), PHP_URL_HOST);
        $name = config('mail.from.name', config('app.name', 'Base Forge'));

        // Soggetto & corpo
        $subj = $data['subject'] ?: ('New contact from '.$data['name']);
        $body = "New contact request\n\n"
              . "Name: {$data['name']}\n"
              . "Email: {$data['email']}\n"
              . "Subject: ".($data['subject'] ?: '-')."\n\n"
              . "Message:\n{$data['message']}\n";

        try {
            Mail::raw($body, function ($m) use ($to, $name, $subj) {
                $m->to($to, $name)->subject($subj);
            });
        } catch (\Throwable $e) {
            Log::error('Contact mail send failed', ['err' => $e->getMessage()]);
            return back()->withInput()
                ->with('error', 'Unable to send your message right now. Please try later.');
        }

        return back()->with('success', 'Thanks! Your message has been sent.');
    }

    /** SITE KEY: prima da config/services, poi da SiteSetting */
    private function recaptchaSite(): ?string
    {
        // usa la stessa nomenclatura del secret per coerenza
        $site = config('services.recaptcha.site_key') ?? config('services.recaptcha.site');
        if ($site) return $site;

        if (class_exists(\App\Models\SiteSetting::class)) {
            return optional(\App\Models\SiteSetting::first())->recaptcha_site_key ?: null;
        }
        return null;
    }

    /** SECRET KEY: prima da config/services, poi da SiteSetting */
    private function recaptchaSecret(): ?string
    {
        $secret = config('services.recaptcha.secret_key') ?? config('services.recaptcha.secret');
        if ($secret) return $secret;

        if (class_exists(\App\Models\SiteSetting::class)) {
            return optional(\App\Models\SiteSetting::first())->recaptcha_secret_key ?: null;
        }
        return null;
    }

    /** Verifica reCAPTCHA v3 (true se ok con soglia >= 0.5) */
    private function verifyRecaptcha(string $secret, string $token, ?string $ip = null): bool
    {
        try {
            $payload = ['secret' => $secret, 'response' => $token];
            if ($ip) $payload['remoteip'] = $ip;

            $resp = Http::asForm()->timeout(8)->post(
                'https://www.google.com/recaptcha/api/siteverify',
                $payload
            );

            if (!$resp->successful()) return false;

            $json  = $resp->json();
            $ok    = (bool)($json['success'] ?? false);
            $score = (float)($json['score'] ?? 0);
            return $ok && $score >= 0.5;
        } catch (\Throwable $e) {
            Log::warning('reCAPTCHA verify error: '.$e->getMessage());
            return false;
        }
    }
}