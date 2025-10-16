<?php

use Illuminate\Support\Facades\Storage;
use App\Models\MediaAsset;

/* -----------------------------
 |  PATH helpers
 * ---------------------------*/
if (!function_exists('media_path_normalize')) {
    /**
     * Normalizza un path per l'aggancio su media_assets.path
     * Accetta: 'builders/a.jpg', '/storage/builders/a.jpg', 'https://.../storage/builders/a.jpg'
     * Ritorna: 'builders/a.jpg'
     */
    function media_path_normalize(?string $path): ?string
    {
        if (!$path) return null;
        $p = parse_url($path, PHP_URL_PATH) ?? $path;   // rimuove schema/host se presenti
        $p = ltrim($p, '/');
        if (str_starts_with($p, 'storage/')) {
            $p = substr($p, strlen('storage/'));        // togli "storage/"
        }
        return $p ?: null;
    }
}

/* -----------------------------
 |  IMG URL (CF Images + fallback webp)
 * ---------------------------*/
if (!function_exists('img_url')) {
    /**
     * Genera l’URL immagine ottimizzato.
     * - Se CF Image è abilitato (config('cdn.use_cloudflare')), usa /cdn-cgi/image/...
     * - Altrimenti fallback a Storage::url, preferendo il .webp locale.
     */
    function img_url(?string $path, ?int $w=null, ?int $h=null, int $q=82, string $fit='cover'): ?string
    {
        if (!$path) return null;

        // URL pubblico origin (es. /storage/...)
        $origin = Storage::disk('public')->url($path);
        $clean  = ltrim(parse_url($origin, PHP_URL_PATH) ?: $origin, '/');

        // Cloudflare Images attivo?
        $useCf = (bool) (config('cdn.use_cloudflare', env('USE_CF_IMAGE', false)));
        if ($useCf) {
            $ops = ['format=auto', "quality={$q}", "fit={$fit}"];
            if ($w) $ops[] = "width={$w}";
            if ($h) $ops[] = "height={$h}";
            return '/cdn-cgi/image/' . implode(',', $ops) . '/' . $clean;
        }

        // Fallback: preferisci .webp se esiste sul disco
        $disk = Storage::disk('public');
        $maybeWebp = preg_replace('/\.(jpe?g|png|gif|bmp)$/i', '.webp', $path);
        if ($maybeWebp && $disk->exists($maybeWebp)) {
            return $disk->url($maybeWebp);
        }
        return $origin;
    }
}

/* -----------------------------
 |  ALT TEXT (da media_assets.alt_text con fallback)
 * ---------------------------*/
if (!function_exists('img_alt')) {
    /**
     * Recupera l'ALT da media_assets.path → alt_text.
     * Accetta un model che abbia image_path *oppure* un path stringa.
     * Fallback: proprietà del model (seo_alt / alt_text / image_alt / name / title) o 'Image'.
     */
    function img_alt($subject, ?string $fallback=null): string
    {
        $path = null;
        $candidates = [];

        if (is_object($subject)) {
            // prova a prendere il path dal model
            $path = $subject->image_path ?? null;

            // fallback possibili dal model
            $candidates = [
                $subject->seo_alt     ?? null,
                $subject->alt_text    ?? null,
                $subject->image_alt   ?? null,
                $subject->name        ?? null,
                $subject->title       ?? null,
            ];
        } elseif (is_string($subject)) {
            $path = $subject;
        }

        // lookup tabella SEO media
        if ($path) {
            $norm = media_path_normalize($path);
            if ($norm) {
                $asset = MediaAsset::where('path', $norm)->first();
                if ($asset && $asset->alt_text) {
                    return (string) $asset->alt_text;
                }
            }
        }

        // fallback esplicito passato
        if ($fallback) return $fallback;

        // altri fallback dal model
        foreach ($candidates as $v) {
            if (!empty($v)) return (string) $v;
        }

        return 'Image';
    }
}

/* -----------------------------
 |  VIDEO embed (come avevi)
 * ---------------------------*/
if (!function_exists('embed_from_url')) {
    function embed_from_url(string $url): ?string {
        if (preg_match('~(?:youtube\.com/watch\?v=|youtu\.be/)([A-Za-z0-9_-]{6,})~', $url, $m)) {
            return 'https://www.youtube.com/embed/'.$m[1];
        }
        if (preg_match('~vimeo\.com/(\d+)~', $url, $m)) {
            return 'https://player.vimeo.com/video/'.$m[1];
        }
        return null;
    }
}

/* -----------------------------
 |  MONEY (come avevi)
 * ---------------------------*/
use App\Support\Currency;

if (!function_exists('money_site')) {
    function money_site(int $cents): string {
        $site = Currency::site();
        return Currency::format($cents, $site['code']);
    }
}

if (!function_exists('money_convert_and_format')) {
    function money_convert_and_format(int $cents, string $from): string {
        $site = Currency::site();
        $conv = Currency::convertCents($cents, strtoupper($from), $site['code'], $site['fx']);
        return Currency::format($conv, $site['code']);
    }
    <?php

use App\Support\Img;

if (!function_exists('img_url')) {
    function img_url(?string $p, ?int $w=null, ?int $h=null, int $q=82, string $fit='cover'): ?string {
        return Img::url($p, $w, $h, $q, $fit);
    }
}

if (!function_exists('img_origin')) {
    function img_origin(?string $p): ?string {
        return Img::origin($p);
    }
}

if (!function_exists('img_alt')) {
    function img_alt($subjectOrPath): ?string {
        return Img::alt($subjectOrPath);
    }
}
}