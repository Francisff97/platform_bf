<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;

class Img
{
    /** Ritorna URL webp se esiste e client lo supporta (aggiungi Vary: Accept via nginx o header) */
    public static function url(string $path): string
    {
        $disk = 'public';
        $webpPath = preg_replace('/\.(jpe?g|png)$/i', '.webp', $path);

        if ($webpPath && Storage::disk($disk)->exists($webpPath)) {
            return Storage::disk($disk)->url($webpPath);
        }
        return Storage::disk($disk)->url($path);
    }
}