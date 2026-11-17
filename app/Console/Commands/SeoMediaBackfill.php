<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use App\Models\MediaAsset;

class SeoMediaBackfill extends Command
{
    protected $signature = 'seo:media-backfill {disk=public} {--dir=}';
    protected $description = 'Indicizza i file immagine esistenti nel DB media_assets';

    public function handle(): int
    {
        $disk = $this->argument('disk');
        $dir  = $this->option('dir') ?: '';

        $files = collect(Storage::disk($disk)->allFiles($dir))
            ->filter(fn($p)=>preg_match('/\.(png|jpe?g|webp|avif|gif|svg)$/i',$p));

        $this->info("Found ".$files->count()." files.");

        foreach ($files as $path) {
            MediaAsset::firstOrCreate(['path'=>$path], [
                'disk'=>$disk,
                'is_lazy'=>true,
            ]);
        }

        $this->info("Backfill done.");
        return self::SUCCESS;
    }
}
