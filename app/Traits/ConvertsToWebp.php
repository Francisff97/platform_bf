<?php

namespace App\Traits;

use Illuminate\Support\Facades\Storage;

class_exists(\Intervention\Image\ImageManager::class); // hint per static analysers

trait ConvertsToWebp
{
    /**
     * Converte un file PNG/JPG presente su un disk in .webp (stesso path, estensione cambiata).
     * Ritorna il path relativo del webp generato, oppure null se fallisce o non applicabile.
     */
    protected function toWebp(string $disk, string $path, int $quality = 75): ?string
    {
        // opzionale: disattiva via env
        if (env('APP_WEBP_ENABLED', true) !== true) {
            return null;
        }

        // Accetta solo png/jpg/jpeg
        if (!preg_match('/\.(png|jpe?g)$/i', $path)) {
            return null;
        }

        // Se la libreria non è installata → esci in silenzio (niente 500)
        if (!class_exists(\Intervention\Image\ImageManager::class)) {
            return null;
        }

        try {
            // Ottieni path assoluto (solo per dischi locali, es. "public")
            $abs = Storage::disk($disk)->path($path);
            if (!is_file($abs)) return null;

            $webpAbs = preg_replace('/\.(png|jpe?g)$/i', '.webp', $abs);
            if (!$webpAbs) return null;

            // Evita lavoro se esiste già ed è “recente”
            if (is_file($webpAbs) && filemtime($webpAbs) >= filemtime($abs)) {
                return $this->relativeFromAbs($disk, $webpAbs);
            }

            // Intervention v3 (GD)
            $managerClass = \Intervention\Image\ImageManager::class;
            $driverClass  = \Intervention\Image\Drivers\Gd\Driver::class;

            /** @var \Intervention\Image\ImageManager $manager */
            $manager = new $managerClass(new $driverClass());
            $image   = $manager->read($abs);

            // Salvataggio WebP
            $image->toWebp($quality)->save($webpAbs);

            return $this->relativeFromAbs($disk, $webpAbs);
        } catch (\Throwable $e) {
            // Non bloccare il salvataggio dei modelli
            \Log::warning('toWebp failed: '.$e->getMessage(), ['path' => $path]);
            return null;
        }
    }

    /**
     * Controlla se esiste il .webp accanto al file dato.
     */
    protected function webpExists(string $disk, string $path): bool
    {
        if (!preg_match('/\.(png|jpe?g)$/i', $path)) return false;
        try {
            $abs = Storage::disk($disk)->path($path);
            $webpAbs = preg_replace('/\.(png|jpe?g)$/i', '.webp', $abs);
            return $webpAbs && is_file($webpAbs);
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Converte path assoluto in relativo allo storage del disk.
     */
    private function relativeFromAbs(string $disk, string $abs): ?string
    {
        $root = rtrim(Storage::disk($disk)->path(''), DIRECTORY_SEPARATOR);
        $abs  = str_replace('\\', '/', $abs);
        $root = str_replace('\\', '/', $root);
        if (str_starts_with($abs, $root)) {
            return ltrim(substr($abs, strlen($root)), '/');
        }
        return null;
        // NB: su S3 non c'è path() locale. Questo trait è pensato per disk locali (es. "public").
    }
}