<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use App\Traits\ConvertsToWebp;

class Slide extends Model
{
    use ConvertsToWebp;

    protected $fillable = [
        'title','subtitle','image_path','cta_label','cta_url','sort_order','is_active'
    ];

    protected static function booted()
    {
        static::saved(function (self $m) {
            if ($m->image_path) {
                $m->toWebp('public', $m->image_path, 75);
            }
        });
    }

    /** {{ $slide->image_url }} */
    public function getImageUrlAttribute(): ?string
    {
        // Slide: 1600 è un buon default (caroselli full-width)
        return $this->imageUrl(1600, null, 85);
    }

    public function imageUrl(?int $w = null, ?int $h = null, int $q = 85): ?string
    {
        if (!$this->image_path) return null;

        if ($this->useCloudflareImage()) {
            return $this->cdnFromDisk($this->image_path, $w, $h, $q);
        }

        return $this->preferWebp($this->image_path);
    }

    /** ========= Helper interni ========= */

    private function useCloudflareImage(): bool
    {
        return (bool) (config('cdn.use_cloudflare', env('USE_CF_IMAGE', true)));
    }

    private function cdnFromDisk(string $diskPath, ?int $w, ?int $h, int $q): string
    {
        $origin = Storage::disk('public')->url($diskPath);
        $path   = ltrim(parse_url($origin, PHP_URL_PATH), '/');

        $params = ['format=auto', "quality={$q}"];
        if ($w) $params[] = "width={$w}";
        if ($h) $params[] = "height={$h}";

        return '/cdn-cgi/image/' . implode(',', $params) . '/' . $path;
    }

    private function preferWebp(?string $path): ?string
    {
        if (!$path) return null;
        $webp = preg_replace('/\.(jpe?g|png|gif|bmp)$/i', '.webp', $path);
        if ($webp && Storage::disk('public')->exists($webp)) {
            return Storage::disk('public')->url($webp);
        }
        return Storage::disk('public')->url($path);
    }
}