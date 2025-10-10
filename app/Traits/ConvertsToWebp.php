<?php

namespace App\Traits;

use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver; // o Imagick se lo hai

trait ConvertsToWebp
{
    protected function toWebp(string $disk, string $path, int $quality = 75): ?string
    {
        if (!preg_match('/\.(png|jpe?g)$/i', $path)) return null;

        $manager = new ImageManager(new Driver());
        $abs = Storage::disk($disk)->path($path);
        if (!is_file($abs)) return null;

        $img = $manager->read($abs)->toWebp($quality);
        $webpPath = preg_replace('/\.(png|jpe?g)$/i', '.webp', $path);

        Storage::disk($disk)->put($webpPath, (string)$img->encode());
        return $webpPath;
    }
}