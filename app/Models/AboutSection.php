<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use App\Traits\ConvertsToWebp;

class AboutSection extends Model
{
    use ConvertsToWebp;

    private const IMG_Q   = 82;
    private const IMG_FIT = 'cover';

    protected $fillable = [
        'title','subtitle','body','image_path','sort_order','is_active'
    ];

    protected $casts = ['is_active' => 'boolean'];

    protected static function booted()
    {
        static::saved(function (self $m) {
            if ($m->image_path) {
                $m->toWebp('public', $m->image_path, 75);
            }
        });
    }

    /** Blade: {{ $about->image_url }}  (nessuna modifica alle view) */
    public function getImageUrlAttribute(): ?string
    {
        if (!$this->image_path) return null;

        $origin = Storage::disk('public')->url($this->image_path);
        $path   = ltrim(parse_url($origin, PHP_URL_PATH), '/');

        if ($this->useCloudflareImage()) {
            $ops = [
                'width=auto', 'dpr=auto',
                'format=auto',
                'quality='.self::IMG_Q,
                'fit='.self::IMG_FIT,
            ];
            return '/cdn-cgi/image/'.implode(',', $ops).'/'.$path;
        }

        return $this->preferWebp($this->image_path);
    }

    /** Variante dimensionata on-demand se ti serve dal controller */
    public function imageUrl(?int $w=null, ?int $h=null, int $q=self::IMG_Q, string $fit=self::IMG_FIT): ?string
    {
        if (!$this->image_path) return null;

        $origin = Storage::disk('public')->url($this->image_path);
        $path   = ltrim(parse_url($origin, PHP_URL_PATH), '/');

        if ($this->useCloudflareImage()) {
            $ops = ['format=auto', "quality={$q}", "fit={$fit}", 'dpr=auto'];
            $ops[] = $w ? "width={$w}" : 'width=auto';
            if ($h) $ops[] = "height={$h}";
            return '/cdn-cgi/image/'.implode(',', $ops).'/'.$path;
        }

        return $this->preferWebp($this->image_path);
    }

    /* ===== Helpers ===== */

    private function useCloudflareImage(): bool
    {
        return (bool) (config('cdn.use_cloudflare', env('USE_CF_IMAGE', true)));
    }

    private function preferWebp(?string $path): ?string
    {
        if (!$path) return null;
        $disk = Storage::disk('public');
        $webp = preg_replace('/\.(jpe?g|png|gif|bmp)$/i', '.webp', $path);
        if ($webp && $disk->exists($webp)) return $disk->url($webp);
        return $disk->url($path);
    }
}