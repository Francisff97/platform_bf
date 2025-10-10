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
    {--only-missing : Alias: process only if .webp is missing (default behavior)}';

    protected $description = 'Convert JPG/PNG (su uno o più path) in WebP con Intervention Image (v2 o v3)';

    public function handle(): int
    {
        $disk     = (string) $this->option('disk');
        $quality  = max(0, min(100, (int) $this->option('quality')));
        $paths    = (array) $this->option('path');
        $force    = (bool) $this->option('force');

        if (!in_array($disk, array_keys(config('filesystems.disks')), true)) {
            $this->error("Disk \"$disk\" non trovato in config/filesystems.php");
            return self::FAILURE;
        }

        // Raccogli i file
        $all = collect($paths ?: Storage::disk($disk)->allFiles())
            ->when($paths, function ($c) use ($disk, $paths) {
                // Se passano più path, union dei file per ognuno
                return collect($paths)->flatMap(fn ($p) => Storage::disk($disk)->allFiles($p));
            })
            ->filter(function ($path) {
                // Solo immagini comuni (puoi aggiungere gif ecc.)
                return preg_match('/\.(jpe?g|png)$/i', $path);
            })
            ->values();

        if ($all->isEmpty()) {
            $this->info('Nessuna immagine da convertire.');
            return self::SUCCESS;
        }

        $this->line("Disk: <info>{$disk}</info>  ·  Quality: <info>{$quality}</info>  ·  Force: <info>".($force?'yes':'no')."</info>");
        $this->line('File trovati: <info>'.$all->count().'</info>');
        $bar = $this->output->createProgressBar($all->count());
        $bar->start();

        // Detect Intervention versione
        $usingV3 = class_exists(\Intervention\Image\ImageManager::class);
        $usingV2 = class_exists(\Intervention\Image\ImageManagerStatic::class);

        if (!$usingV3 && !$usingV2) {
            $this->newLine();
            $this->error('Intervention Image non è installato. Esegui: composer require intervention/image');
            return self::FAILURE;
        }

        // Se su server c’è GD/Imagick?
        $driverInfo = 'n/d';
        if ($usingV3) {
            $driverInfo = class_exists(\Intervention\Image\Drivers\Imagick\Driver::class) ? 'imagick' : 'gd';
        } else {
            // v2 non espone driver in modo semplice, ma in genere GD è il default
            $driverInfo = extension_loaded('imagick') ? 'imagick' : 'gd';
        }
        $this->newLine();
        $this->line('Intervention: <info>'.($usingV3?'v3':'v2')."</info>  ·  Driver: <info>{$driverInfo}</info>");

        foreach ($all as $path) {
            try {
                $webpPath = preg_replace('/\.(jpe?g|png)$/i', '.webp', $path);

                if (!$force && Storage::disk($disk)->exists($webpPath)) {
                    $bar->advance();
                    continue;
                }

                $abs = Storage::disk($disk)->path($path);

                if ($usingV3) {
                    // === Intervention v3 ===
                    // Driver: usa Imagick se presente, altrimenti GD
                    if (class_exists(\Intervention\Image\Drivers\Imagick\Driver::class) && extension_loaded('imagick')) {
                        $driver = new \Intervention\Image\Drivers\Imagick\Driver();
                    } else {
                        $driver = new \Intervention\Image\Drivers\Gd\Driver();
                    }
                    $manager = new \Intervention\Image\ImageManager($driver);

                    $img = $manager->read($abs)->toWebp($quality);
                    // $img è \Intervention\Image\EncodedImage
                    Storage::disk($disk)->put($webpPath, (string) $img);
                } else {
                    // === Intervention v2 ===
                    // Static API
                    $img = \Intervention\Image\ImageManagerStatic::make($abs)->encode('webp', $quality);
                    Storage::disk($disk)->put($webpPath, (string) $img);
                }
            } catch (\Throwable $e) {
                // Non bloccare l’intero batch: logga e passa oltre
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