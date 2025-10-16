<?php
declare(strict_types=1);

use App\Support\Currency;
use App\Support\Img;

/* -----------------------------------------
 |  EMBED VIDEO (YouTube / Vimeo)
 * -----------------------------------------*/
if (!function_exists('embed_from_url')) {
    /**
     * Restituisce l'URL di embed dato un link YouTube/Vimeo.
     * Altrimenti null (usa il link diretto).
     */
    function embed_from_url(string $url): ?string
    {
        if (preg_match('~(?:youtube\.com/watch\?v=|youtu\.be/)([A-Za-z0-9_-]{6,})~', $url, $m)) {
            return 'https://www.youtube.com/embed/' . $m[1];
        }
        if (preg_match('~vimeo\.com/(\d+)~', $url, $m)) {
            return 'https://player.vimeo.com/video/' . $m[1];
        }
        return null;
    }
}

/* -----------------------------------------
 |  MONEY (senza conversione / con conversione)
 * -----------------------------------------*/
if (!function_exists('money_site')) {
    /**
     * Format centesimi nella valuta del sito (senza conversione FX).
     */
    function money_site(int $cents): string
    {
        $site = Currency::site(); // ['code' => 'EUR', 'fx' => ...]
        return Currency::format($cents, $site['code']);
    }
}

if (!function_exists('money_convert_and_format')) {
    /**
     * Converte da $from alla valuta del sito e formatta.
     */
    function money_convert_and_format(int $cents, string $from): string
    {
        $site = Currency::site(); // ['code' => 'EUR', 'fx' => ...]
        $to   = $site['code'];
        $fx   = $site['fx'] ?? null;

        $conv = Currency::convertCents($cents, strtoupper($from), $to, $fx);
        return Currency::format($conv, $to);
    }
}

/* -----------------------------------------
 |  IMAGES (Cloudflare Image opzionale + fallback WebP/Storage)
 |  Tutti gli helper delegano ad App\Support\Img
 * -----------------------------------------*/
if (!function_exists('img_url')) {
    /**
     * URL ottimizzato per un'immagine su disk "public".
     * Se USE_CF_IMAGE/config('cdn.use_cloudflare') è true → /cdn-cgi/image/…
     * altrimenti .webp se esiste, oppure Storage::url() dell’originale.
     */
    function img_url(?string $path, ?int $w = null, ?int $h = null, int $q = 82, string $fit = 'cover'): ?string
    {
        return Img::url($path, $w, $h, $q, $fit);
    }
}

if (!function_exists('img_alt')) {
    /**
     * Recupera l'ALT dal backend (SEO → Media) se disponibile,
     * altrimenti ritorna il fallback passato.
     *
     * Accetta sia un path (es. "builders/foo.jpg") sia un URL "/storage/…".
     */
    function img_alt(?string $pathOrUrl, ?string $fallback = null): ?string
    {
        return Img::alt($pathOrUrl, $fallback);
    }
}

/* ----- Preset comodi per le Blade (dimensioni fisse coerenti) ----- */

if (!function_exists('img_avatar')) {
    /**
     * Avatar quadrato (default 224x224).
     */
    function img_avatar(?string $path, int $size = 224, int $q = 82): ?string
    {
        return Img::url($path, $size, $size, $q, 'cover');
    }
}

if (!function_exists('img_grid')) {
    /**
     * Card in griglia (es. builder index) ~ 720x300.
     */
    function img_grid(?string $path, int $w = 720, int $h = 300, int $q = 82): ?string
    {
        return Img::url($path, $w, $h, $q, 'cover');
    }
}

if (!function_exists('img_hero')) {
    /**
     * Hero 16:9 (default 1600x900). Regola w/h a piacere nelle blade.
     */
    function img_hero(?string $path, int $w = 1600, int $h = 900, int $q = 82): ?string
    {
        return Img::url($path, $w, $h, $q, 'cover');
    }
}