<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Storage;
use App\Traits\ConvertsToWebp;

class Pack extends Model
{
    use HasFactory, ConvertsToWebp;

    protected $fillable = [
        'title','slug','excerpt','description',
        'image_path',
        'price_cents','currency',
        'is_featured','status','published_at',
        'category_id',
        'builder_id',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'published_at'=> 'datetime',
    ];

    protected static function booted()
    {
        static::saved(function (self $m) {
            if ($m->image_path) {
                $m->toWebp('public', $m->image_path, 75);
            }
        });
    }

    /** {{ $pack->image_url }} */
    public function getImageUrlAttribute(): ?string
    {
        // Card grid: 800px è un buon default desktop
        return $this->imageUrl(800, null, 85);
    }

    /**
     * URL immagine dinamico (Cloudflare) con fallback locale.
     * Esempi Blade:
     *  <img src="{{ $pack->imageUrl(480) }}" srcset="{{ $pack->imageSrcset() }}" sizes="(min-width:1024px) 33vw, 90vw">
     */
    public function imageUrl(?int $w = null, ?int $h = null, int $q = 85): ?string
    {
        if (!$this->image_path) return null;

        if ($this->useCloudflareImage()) {
            return $this->cdnFromDisk($this->image_path, $w, $h, $q);
        }

        return $this->preferWebp($this->image_path);
    }

    /**
     * Utility per costruire uno srcset responsive “standard”
     * (400/800/1200). Usala se vuoi senza toccare le Blade:
     *  <img src="{{ $pack->imageUrl(800) }}" srcset="{{ $pack->imageSrcset() }}" ...>
     */
    public function imageSrcset(array $widths = [400, 800, 1200], int $q = 85): ?string
    {
        if (!$this->image_path) return null;

        $parts = [];
        foreach ($widths as $w) {
            $parts[] = $this->imageUrl($w, null, $q) . " {$w}w";
        }
        return implode(', ', $parts);
    }

    /** ==== Relations (come avevi) ==== */
    public function scopePublished($q){ return $q->where('status','published'); }
    public function category(){ return $this->belongsTo(Category::class); }
    public function builder(){ return $this->belongsTo(Builder::class); }

    public function tutorials()
    {
        return $this->morphMany(\App\Models\Tutorial::class, 'tutorialable')
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    /** ========= Helper interni ========= */

    private function useCloudflareImage(): bool
    {
        return (bool) (config('cdn.use_cloudflare', env('USE_CF_IMAGE', true)));
    }

    private function cdnFromDisk(string $diskPath, ?int $w, ?int $h, int $q): string
    {
        $origin = Storage::disk('public')->url($diskPath); // /storage/...
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