<?php

namespace App\Support;

use App\Models\MediaAsset;
use Illuminate\Support\Facades\Storage;

class MediaIngestor
{
    public static function ingest(string $disk, string $path, ?string $alt = null, ?bool $isLazy = null): MediaAsset
    {
        $path = ltrim($path, '/');

        $ma = MediaAsset::firstOrCreate(['path'=>$path], [
            'disk'=>$disk,
            'is_lazy'=>true,
        ]);

        try {
            if (is_null($ma->checksum) && Storage::disk($disk)->exists($path)) {
                $ma->checksum = hash('sha256', Storage::disk($disk)->get($path));
            }
        } catch (\Throwable $e) { /* ignore */ }

        if (!is_null($alt))    $ma->alt_text = $alt;
        if (!is_null($isLazy)) $ma->is_lazy  = (bool) $isLazy;

        $ma->save();
        return $ma;
    }
}
