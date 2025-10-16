<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Storage;

use App\Traits\ConvertsToWebp;

class Builder extends Model
{
    use HasFactory, ConvertsToWebp;

    // qualità e fit di default
    private const IMG_Q   = 82;
    private const IMG_FIT = 'cover';

    protected $fillable = ['name','slug','team','image_path','skills','description'];
    protected $casts    = ['skills'=>'array'];

    protected static function booted()
    {
        static::saved(function (self $m) {
            if ($m->image_path) {
                // fallback WebP locale per quando CF è off
                $m->toWebp('public', $m->image_path, 75);
            }
        });
    }

    /** accessor generico (non usato nelle blade, ma utile come default) */
    public function getImageUrlAttribute(): ?string
    {
        return $this->imageUrl(); // nessuna dimensione → solo optimizations
    }

    /** === URL helper generico con dimensioni opzionali === */
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

    /** === Preset per le blade (dimensioni fisse) === */

    // Card nella lista builders (h-44 ~ 176px; larga → 720x300)
    public function gridSrc(): ?string
    {
        return $this->imageUrl(720, 300);
    }

    // Immagine nella show (aspect 4/3 circa)
    public function showSrc(): ?string
    {
        return $this->imageUrl(1200, 900);
    }

    // Avatar tondo piccolo (lista/miniature)
    public function avatarSrc(int $size = 224): ?string
    {
        return $this->imageUrl($size, $size, self::IMG_Q, 'cover');
    }

    /** === Relazioni === */
    public function packs()
    {
        return $this->hasMany(Pack::class);
    }

    /** === Helpers interni === */
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