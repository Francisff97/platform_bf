<?php
// app/Support/MediaIngestor.php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class MediaIngestor
{
    /**
     * @param  UploadedFile|string       $file   UploadedFile dal form OPPURE un path/URL relativo
     * @param  string                    $destRelativePath  es. 'packs/foo/bar.png'
     * @param  string|array|null         $alt
     * @return string  percorso relativo salvato nel disk 'public' (es. 'packs/foo/bar.png')
     */
    public static function ingest(UploadedFile|string $file, string $destRelativePath, string|array|null $alt = null): string
    {
        // --- normalizza ALT ---
        if (is_array($alt)) {
            $alt = trim(implode(' ', array_filter(array_map('trim', $alt))));
            if ($alt === '') $alt = null;
        } elseif (!is_null($alt)) {
            $alt = trim((string) $alt);
            if ($alt === '') $alt = null;
        }

        $disk = 'public';
        $stored = $destRelativePath;

        // --- salvataggio file ---
        if ($file instanceof UploadedFile) {
            Storage::disk($disk)->putFileAs(
                dirname($destRelativePath),
                $file,
                basename($destRelativePath)
            );
        } else {
            // È una stringa: path tipo '/storage/...' o 'packs/..../foo.png'
            $candidate = ltrim(preg_replace('~^/?storage/~', '', $file), '/');

            if (Storage::disk($disk)->exists($candidate)) {
                $stored = $candidate; // già sul disk 'public'
            } elseif (is_file(public_path($file))) {
                // Copia da public path al disk 'public' nella destinazione richiesta
                Storage::disk($disk)->put($destRelativePath, file_get_contents(public_path($file)));
                $stored = $destRelativePath;
            } else {
                // fallback: crea dir e usa comunque destRelativePath (se poi non esiste, almeno non crasha)
                Storage::disk($disk)->makeDirectory(dirname($destRelativePath));
                $stored = $destRelativePath;
            }
        }

        // --- genera .webp accanto (se PNG/JPG) ---
        if (preg_match('/\.(png|jpe?g)$/i', $stored)) {
            try {
                $abs = Storage::disk($disk)->path($stored);
                $webpRel = preg_replace('/\.(png|jpe?g)$/i', '.webp', $stored);

                $manager = new ImageManager(new Driver());
                $img = $manager->read($abs);

                Storage::disk($disk)->put($webpRel, (string) $img->toWebp(75));
            } catch (\Throwable $e) {
                // silenzioso: non bloccare il flow se manca GD/Intervention
            }
        }

        // --- salva alt opzionale in tabella media_assets (se esiste) ---
        if (class_exists(\App\Models\MediaAsset::class)) {
            \App\Models\MediaAsset::updateOrCreate(
                ['path' => $stored],
                ['alt'  => $alt]
            );
        }

        return $stored;
    }
}