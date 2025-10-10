<?php
namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class MediaIngestor
{
    /**
     * @param  UploadedFile  $file
     * @param  string        $destRelativePath   es. 'packs/foo/bar.png'
     * @param  string|array|null $alt
     * @return string  // percorso relativo salvato (es. 'packs/foo/bar.png')
     */
    public static function ingest(UploadedFile $file, string $destRelativePath, $alt = null): string
    {
        // 🔒 normalizza $alt ad una stringa (o null)
        if (is_array($alt)) {
            $alt = trim(implode(' ', array_filter(array_map('trim', $alt))));
            if ($alt === '') $alt = null;
        } elseif (!is_null($alt)) {
            $alt = trim((string) $alt);
            if ($alt === '') $alt = null;
        }

        // salva file sul disk 'public'
        $stored = Storage::disk('public')->putFileAs(
            dirname($destRelativePath),
            $file,
            basename($destRelativePath)
        );

        // se hai una tabella media_assets, salva anche l'alt qui (facoltativo)
        if (class_exists(\App\Models\MediaAsset::class)) {
            \App\Models\MediaAsset::updateOrCreate(
                ['path' => $stored],
                ['alt' => $alt]
            );
        }

        return $stored;
    }
}