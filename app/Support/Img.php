<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;

class Img
{
    /**
     * Ritorna un URL ottimizzato:
     * - se abilitato: Cloudflare Image Resizing (/cdn-cgi/image/…)
     * - altrimenti: fallback al file su storage, preferendo .webp se presente
     */
    public static function url(?string $path, ?int $w=null, ?int $h=null, int $q=82, string $fit='cover'): ?string
    {
        if (!$path) return null;

        // URL pubblico allo storage (origin)
        $originUrl = self::origin($path);
        $pathOnly  = ltrim(parse_url($originUrl, PHP_URL_PATH) ?: $path, '/');

        // flag: abilita CF e permetti override con ?no_cf=1
        $useCF = (bool) config('cdn.use_cloudflare', env('USE_CF_IMAGE', false))
                 && !request()->boolean('no_cf');

        if ($useCF) {
            $ops = ['format=auto', "quality={$q}", "fit={$fit}"];
            if ($w) $ops[] = "width={$w}";
            if ($h) $ops[] = "height={$h}";
            return '/cdn-cgi/image/'.implode(',', $ops).'/'.$pathOnly;
        }

        // fallback locale (preferisci webp se c'è)
        return self::preferWebp($path);
    }

    /** URL pubblico al file originale su storage (origin) */
    public static function origin(?string $path): ?string
    {
        if (!$path) return null;
        return Storage::disk('public')->url($path);
    }

    /** ALT centralizzato: guarda in media_assets.alt_text, poi title/name/alt_text del soggetto, altrimenti null */
    public static function alt($subjectOrPath): ?string
    {
        try {
            $path = null;
            if (is_object($subjectOrPath) && isset($subjectOrPath->image_path)) {
                $path = $subjectOrPath->image_path;
            } elseif (is_string($subjectOrPath)) {
                $path = $subjectOrPath;
            }

            if ($path && class_exists(\App\Models\MediaAsset::class)) {
                $m = \App\Models\MediaAsset::where('path', $path)->first();
                if ($m && $m->alt_text) return $m->alt_text;
            }
        } catch (\Throwable $e) {
            // no-op
        }

        if (is_object($subjectOrPath)) {
            foreach (['alt_text','title','name'] as $f) {
                if (isset($subjectOrPath->{$f}) && $subjectOrPath->{$f}) return $subjectOrPath->{$f};
            }
        }

        return null;
    }

    /** Preferisci .webp se esiste, altrimenti file originale */
    protected static function preferWebp(string $path): string
    {
        $disk = Storage::disk('public');
        $webp = preg_replace('/\.(jpe?g|png|gif|bmp)$/i', '.webp', $path);
        if ($webp && $disk->exists($webp)) return $disk->url($webp);
        return $disk->url($path);
    }
}