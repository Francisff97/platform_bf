<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class ImagesConvertToWebp extends Command
{
    protected $signature = 'images:to-webp
        {--disk=public : Storage disk}
        {--quality=75  : WebP quality (0-100)}
        {--force       : Rebuild even if .webp exists}
        {--only-missing : Alias: process only if .webp is missing (default behavior)}
        {--path=*      : Optional one or more paths (dirs/files) to scan within the disk}';

    protected $description = 'Convert JPG/PNG to WebP (local disk). Compatible with Intervention Image v2/v3.';

    public function handle(): int
    {
        $disk        = (string) $this->option('disk');
        $quality     = max(0, min(100, (int) $this->option('quality')));
        $paths       = (array) $this->option('path'); // array of dirs/files (optional)
        $force       = (bool) $this->option('force');
        $onlyMissing = (bool) $this->option('only-missing'); // alias, no-op (default already missing-only)

        if (!array_key_exists($disk, config('filesystems.disks'))) {
            $this->error("Disk \"{$disk}\" non trovato in config/filesystems.php");
            return self::FAILURE;
        }

        // Raccogli i file da processare
        $files = collect();

        if (!empty($paths)) {
            foreach ($paths as $p) {
                // Se $p è una directory sul disk, prendi tutti i file sotto
                if (Storage::disk($disk)->exists($p) && !preg_match('~/[^/]+\.[a-z0-9]{2,5}$~i', $p)) {
                    $files = $files->merge(Storage::disk($disk)->allFiles($p));
                } else {
                    // trattalo come singolo file relativo al disk
                    $files->push($p);
                }
            }
        } else {
            $files = collect(Storage::disk($disk)->allFiles());
        }

        $all = $files
            ->filter(fn ($path) => preg_match('/\.(jpe?g|png)$/i', $path))
            ->values();

        if ($all->isEmpty()) {
            $this->info('Nessuna immagine JPG/PNG trovata.');
            return self::SUCCESS;
        }

        $this->line("Disk: <info>{$disk}</info> · Quality: <info>{$quality}</info> · Force: <info>".($force ? 'yes' : 'no')."</info>".($onlyMissing ? ' · only-missing' : ''));

        // Rileva Intervention installata
        $usingV3 = class_exists(\Intervention\Image\ImageManager::class);
        $usingV2 = class_exists(\Intervention\Image\ImageManagerStatic::class);

        if (!$usingV3 && !$usingV2) {
            $this->error('Intervention Image non installato. Esegui: composer require intervention/image');
            return self::FAILURE;
        }

        $driverInfo = 'n/d';
        if ($usingV3) {
            $driverInfo = class_exists(\Intervention\Image\Drivers\Imagick\Driver::class) && extension_loaded('imagick')
                ? 'imagick'
                : 'gd';
        } else {
            $driverInfo = extension_loaded('imagick') ? 'imagick' : 'gd';
        }
        $this->line('Intervention: <info>'.($usingV3 ? 'v3' : 'v2')."</info> · Driver: <info>{$driverInfo}</info>");

        $bar = $this->output->createProgressBar($all->count());
        $bar->start();

        foreach ($all as $path) {
            try {
                $webpPath = preg_replace('/\.(jpe?g|png)$/i', '.webp', $path);

                // Default: SOLO MANCANTI. Se --force, rigenera.
                if (!$force && Storage::disk($disk)->exists($webpPath)) {
                    $bar->advance();
                    continue;
                }

                $abs = Storage::disk($disk)->path($path); // valido per disk locali (es. "public")
                if (!is_file($abs)) {
                    $bar->advance();
                    continue;
                }

                if ($usingV3) {
                    // v3: crea manager con driver dinamico
                    if (class_exists(\Intervention\Image\Drivers\Imagick\Driver::class) && extension_loaded('imagick')) {
                        $driver = new \Intervention\Image\Drivers\Imagick\Driver();
                    } else {
                        $driver = new \Intervention\Image\Drivers\Gd\Driver();
                    }
                    $manager = new \Intervention\Image\ImageManager($driver);

                    $encoded = $manager->read($abs)->toWebp($quality); // EncodedImage
                    Storage::disk($disk)->put($webpPath, (string) $encoded);
                } else {
                    // v2: API statica
                    $encoded = \Intervention\Image\ImageManagerStatic::make($abs)->encode('webp', $quality);
                    Storage::disk($disk)->put($webpPath, (string) $encoded);
                }
            } catch (\Throwable $e) {
                $this->newLine();
                $this->warn("Errore su {$path}: ".$e->getMessage());
            } finally {
                $bar->advance();
            }
        }

        $bar->finish();
        $this->newLine(2);
        $this->info('Conversione completata ✅');

        return self::SUCCESS;
    }
}