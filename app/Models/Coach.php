<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Storage;

use App\Traits\ConvertsToWebp;

class Coach extends Model
{
    use HasFactory, ConvertsToWebp;

    private const IMG_Q   = 82;
    private const IMG_FIT = 'cover';

    protected $fillable = ['name','slug','team','image_path','skills'];
    protected $casts    = ['skills' => 'array'];

    protected static function booted()
    {
        static::saved(function (self $m) {
            if ($m->image_path) {
                $m->toWebp('public', $m->image_path, 75);
            }
        });
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->imageUrl();
    }

    /** Generico */
    public function imageUrl(?int $w=null, ?int $h=null, int $q=self::IMG_Q, string $fit=self::IMG_FIT): ?string
    {
        if (!$this->image_path) return null;

        $origin = Storage::disk('public')->url($this->image_path);
        $path   = ltrim(parse_url($origin, PHP_URL_PATH), '/');

        if ($this->useCloudflareImage()) {
            $ops = ['format=auto', "quality={$q}", "fit={$fit}"];
            if ($w) $ops[] = "width={$w}";
            if ($h) $ops[] = "height={$h}";
            return '/cdn-cgi/image/'.implode(',', $ops).'/'.$path;
        }
        return $this->preferWebp($this->image_path);
    }

    /** Preset per blade */

    // Avatar rotondo nelle card (h/w ~ 112 → serviamo un po’ più grande)
    public function avatarSrc(int $size = 224): ?string
    {
        return $this->imageUrl($size, $size, self::IMG_Q, 'cover');
    }

    // Immagine laterale nella show (sm:h-72 → 960x540 16:9)
    public function sideSrc(): ?string
    {
        return $this->imageUrl(960, 540);
    }

    /** Relazioni */
    public function prices()  { return $this->hasMany(CoachPrice::class); }

    public function tutorials()
    {
        return $this->morphMany(\App\Models\Tutorial::class, 'tutorialable')
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    /** Helpers */
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