<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

// Intervention Image v3
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class MediaIngestor
{
    /**
     * Ingest di un file (UploadedFile OPPURE path stringa già esistente).
     *
     * @param  UploadedFile|string      $file   UploadedFile dal form OPPURE 'packs/foo.png' / '/storage/packs/foo.png'
     * @param  string|null              $destRelativePath  es. 'packs/foo/bar.png' (se null viene generato)
     * @param  string|array|null        $alt
     * @param  string                   $disk  default 'public'
     * @return string|null              percorso relativo salvato nel disk (es. 'packs/foo/bar.png') oppure null se fallisce
     */
    public static function ingest(UploadedFile|string $file, ?string $destRelativePath = null, string|array|null $alt = null, string $disk = 'public'): ?string
    {
        // --- normalizza ALT ---
        if (is_array($alt)) {
            $alt = trim(implode(' ', array_filter(array_map('trim', $alt))));
            if ($alt === '') $alt = null;
        } elseif (!is_null($alt)) {
            $alt = trim((string) $alt);
            if ($alt === '') $alt = null;
        }

        // --- genera nome se non passato ---
        if ($destRelativePath === null) {
            $base = 'uploads/'.date('Y/m');
            $name = uniqid().self::extensionFrom($file);
            $destRelativePath = $base.'/'.$name;
        }

        // Assicura la directory di destinazione
        Storage::disk($disk)->makeDirectory(dirname($destRelativePath));

        $stored = null;

        // --- salva il file ---
        if ($file instanceof UploadedFile) {
            Storage::disk($disk)->putFileAs(
                dirname($destRelativePath),
                $file,
                basename($destRelativePath)
            );
            $stored = $destRelativePath;
        } else {
            // è una stringa: può essere '/storage/...' oppure 'packs/...' ecc.
            $candidate = ltrim(preg_replace('~^/?storage/~', '', (string)$file), '/');

            if (Storage::disk($disk)->exists($candidate)) {
                // già nel disk
                $stored = $candidate;
            } elseif (is_string($file) && is_file(public_path($file))) {
                // file dentro public: copialo nel disk alla destinazione richiesta
                Storage::disk($disk)->put($destRelativePath, @file_get_contents(public_path($file)));
                $stored = $destRelativePath;
            } elseif (is_string($file) && is_file($file)) {
                // path assoluto leggibile
                Storage::disk($disk)->put($destRelativePath, @file_get_contents($file));
                $stored = $destRelativePath;
            } else {
                // sorgente non valida -> NON creare file vuoti
                logger()->warning('MediaIngestor: sorgente non valida (string)', ['file' => $file]);
                return null;
            }
        }

        // --- sanity check: niente file 0 byte ---
        try {
            $abs = Storage::disk($disk)->path($stored);
            if (!is_file($abs) || filesize($abs) === 0) {
                logger()->warning('MediaIngestor: file salvato ma vuoto, annullo', ['path' => $stored]);
                Storage::disk($disk)->delete($stored);
                return null;
            }
        } catch (\Throwable $e) {
            logger()->warning('MediaIngestor: check size fallito', ['e' => $e->getMessage()]);
            // se non riesco a verificare, proseguo comunque
        }

        // --- genera WEBP accanto se PNG/JPG ---
        self::tryMakeWebp($disk, $stored, quality: 75);

        // --- metadati ALT su tabella media_assets se esiste ---
        if (class_exists(\App\Models\MediaAsset::class)) {
            \App\Models\MediaAsset::updateOrCreate(['path' => $stored], ['alt' => $alt]);
        }

        // Debug minimale opzionale (commenta se non ti serve)
        // session()->flash('debug_upload', [
        //   'stored' => $stored,
        //   'disk'   => $disk,
        //   'exists' => Storage::disk($disk)->exists($stored),
        // ]);

        return $stored;
    }

    /** Prova a generare il .webp accanto all’originale (silenziosa se fallisce). */
    public static function tryMakeWebp(string $disk, string $relativePath, int $quality = 75): void
    {
        if (!preg_match('/\.(png|jpe?g)$/i', $relativePath)) return;

        try {
            $abs = Storage::disk($disk)->path($relativePath);
            if (!is_file($abs)) return;

            $webpRel = preg_replace('/\.(png|jpe?g)$/i', '.webp', $relativePath);

            // se già esiste, esci
            if (Storage::disk($disk)->exists($webpRel)) return;

            $manager = new ImageManager(new Driver()); // GD
            $img     = $manager->read($abs);

            Storage::disk($disk)->put($webpRel, (string) $img->toWebp($quality));
        } catch (\Throwable $e) {
            logger()->warning('MediaIngestor: toWebp() fallita', [
                'path' => $relativePath,
                'err'  => $e->getMessage(),
            ]);
            // non bloccare il flusso
        }
    }

    /** Ritorna estensione coerente dal tipo input (UploadedFile|string), default .bin */
    protected static function extensionFrom(UploadedFile|string $file): string
    {
        if ($file instanceof UploadedFile) {
            $ext = strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: 'bin');
            return '.'.$ext;
        }
        if (is_string($file) && preg_match('/\.[a-z0-9]{2,5}$/i', $file, $m)) {
            return $m[0];
        }
        return '.bin';
    }
}