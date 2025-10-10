<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class ImagesConvertToWebp extends Command
{
    protected $signature = 'images:to-webp {--disk=public} {--quality=75}';
    protected $description = 'Convert all PNG/JPG in disk to WebP';

    public function handle()
    {
        $disk = $this->option('disk');
        $quality = (int)$this->option('quality');

        $manager = new ImageManager(new Driver());

        $files = collect(Storage::disk($disk)->allFiles())
            ->filter(fn($f) => preg_match('/\.(png|jpe?g)$/i', $f));

        $bar = $this->output->createProgressBar($files->count());
        $bar->start();

        foreach ($files as $path) {
            try {
                $abs = Storage::disk($disk)->path($path);
                $img = $manager->read($abs)->toWebp($quality);
                $webp = preg_replace('/\.(png|jpe?g)$/i', '.webp', $path);
                Storage::disk($disk)->put($webp, (string)$img->encode());
            } catch (\Throwable $e) {
                $this->warn("Error $path: ".$e->getMessage());
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        $this->info('Done.');
        return self::SUCCESS;
    }
}