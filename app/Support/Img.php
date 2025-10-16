<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;

class Img
{
    public static function url(?string $path, ?int $w=null, ?int $h=null, int $q=82, string $fit='cover'): ?string
    {
        if (!$path) return null;

        // Origin (funziona sempre, anche in dev)
        $origin = Storage::disk('public')->url($path);

        // CF disattivo → preferisci webp locale
        if (!static::useCF()) {
            return static::preferWebp($path);
        }

        // CF attivo → costruisci URL assoluto same-origin
        $ops = ['format=auto',"quality={$q}","fit={$fit}"];
        if ($w) $ops[] = "width={$w}";
        if ($h) $ops[] = "height={$h}";

        $host = request()?->getSchemeAndHttpHost() ?: rtrim(config('app.url'), '/');
        $p    = ltrim(parse_url($origin, PHP_URL_PATH) ?: '', '/');

        return "{$host}/cdn-cgi/image/".implode(',', $ops)."/{$p}";
    }

    public static function preferWebp(string $path): string
    {
        $disk = Storage::disk('public');
        $webp = preg_replace('/\.(jpe?g|png|gif|bmp)$/i', '.webp', $path);
        return ($webp && $disk->exists($webp)) ? $disk->url($webp) : $disk->url($path);
    }

    private static function useCF(): bool
    {
        // config/cdn.php → ['use_cloudflare' => env('USE_CF_IMAGE', false)]
        return (bool) config('cdn.use_cloudflare', (bool) env('USE_CF_IMAGE', false));
    }
}