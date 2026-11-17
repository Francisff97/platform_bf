<?php

namespace App\Support;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class WebIngestor
{
    /**
     * Scarica un’immagine da URL e la salva nel disk, generando anche il .webp.
     *
     * @return string percorso relativo nel disk (es. 'remote/2025/10/uniqid.png')
     */
    public static function ingestFromUrl(string $url, ?string $destRelativePath = null, ?string $alt = null, string $disk = 'public'): string
    {
        if ($destRelativePath === null) {
            $ext = self::extFromUrl($url) ?: 'png';
            $destRelativePath = 'remote/'.date('Y/m').'/'.uniqid().'.'.$ext;
        }

        try {
            $res = Http::timeout(10)->get($url);
            if ($res->successful()) {
                Storage::disk($disk)->makeDirectory(dirname($destRelativePath));
                Storage::disk($disk)->put($destRelativePath, $res->body());
                MediaIngestor::tryMakeWebp($disk, $destRelativePath, 75);

                if (class_exists(\App\Models\MediaAsset::class)) {
                    \App\Models\MediaAsset::updateOrCreate(['path' => $destRelativePath], ['alt' => $alt]);
                }
                return $destRelativePath;
            }
        } catch (\Throwable $e) {
            // ignora errori di rete
        }

        // fallback “vuoto” per non rompere i controller
        Storage::disk($disk)->put($destRelativePath, '');
        return $destRelativePath;
    }

    protected static function extFromUrl(string $url): ?string
    {
        if (preg_match('/\.(png|jpe?g|gif|webp|avif|bmp|svg)(?:\?|#|$)/i', $url, $m)) {
            return strtolower($m[1]);
        }
        return null;
    }
}