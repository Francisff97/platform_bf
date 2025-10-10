<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    public function submit(Request $r)
    {
        // Honeypot: se "company" è pieno, fingo successo (spam bot)
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

        // reCAPTCHA v3 (opzionale): se troviamo una secret key, verifichiamo
        $secret = $this->recaptchaSecret();
        if ($secret && $r->filled('g-recaptcha-response')) {
            if (!$this->verifyRecaptcha($secret, $r->string('g-recaptcha-response'))) {
                return back()
                    ->withInput()
                    ->with('error', 'Recaptcha validation failed. Please try again.');
            }
        }

        // Dove inviare l'email (metti quello che vuoi)
        $to   = config('mail.from.address'); // o la tua admin mail fissa
        $name = config('mail.from.name', config('app.name'));

        // Soggetto
        $subj = $data['subject'] ?: ('New contact from '.$data['name']);

        // Corpo semplice (puoi sostituire con un Mailable)
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
            return back()
                ->withInput()
                ->with('error', 'Unable to send your message right now. Please try later.');
        }

        return back()->with('success', 'Thanks! Your message has been sent.');
    }

    /** Prova a prendere la SITE KEY: prima da config/services, poi da SiteSetting (se esiste) */
    private function recaptchaSite(): ?string
    {
        $site = config('services.recaptcha.site_key');
        if ($site) return $site;

        if (class_exists(\App\Models\SiteSetting::class)) {
            return optional(\App\Models\SiteSetting::first())->recaptcha_site_key ?: null;
        }
        return null;
    }

    /** Prova a prendere la SECRET KEY: prima da config/services, poi da SiteSetting (se esiste) */
    private function recaptchaSecret(): ?string
    {
        $secret = config('services.recaptcha.secret_key');
        if ($secret) return $secret;

        if (class_exists(\App\Models\SiteSetting::class)) {
            return optional(\App\Models\SiteSetting::first())->recaptcha_secret_key ?: null;
        }
        return null;
    }

    /** Verifica reCAPTCHA v3 (ritorna true se ok/sufficient score) */
    private function verifyRecaptcha(string $secret, string $token): bool
    {
        try {
            $resp = Http::asForm()->post(
                'https://www.google.com/recaptcha/api/siteverify',
                ['secret' => $secret, 'response' => $token]
            );

            if (!$resp->successful()) return false;

            $json  = $resp->json();
            $ok    = (bool)($json['success'] ?? false);
            $score = (float)($json['score'] ?? 0);

            // Metti la soglia che preferisci (0.5–0.7 tipico)
            return $ok && $score >= 0.5;
        } catch (\Throwable $e) {
            return false;
        }
    }
}