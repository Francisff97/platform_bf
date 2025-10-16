<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Storage;
use App\Traits\ConvertsToWebp;

class Pack extends Model
{
    use HasFactory, ConvertsToWebp;

    // ---- CONFIG IMMAGINI ----
    const IMG_QUALITY      = 82;                           // qualità default
    const CARD_WIDTHS      = [320, 480, 768, 1024];        // grid/list
    const DETAIL_WIDTHS    = [640, 960, 1280, 1600, 1920]; // pagina dettaglio

    protected $fillable = [
        'title','slug','excerpt','description',
        'image_path',
        'price_cents','currency',
        'is_featured','status','published_at',
        'category_id','builder_id',
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

    /* -------------------------------------------------------
     |  ACCESSOR STANDARD
     |  {{ $pack->image_url }} = versione "card" a 800px
     * ------------------------------------------------------*/
    public function getImageUrlAttribute(): ?string
    {
        return $this->imageUrl(800, null, static::IMG_QUALITY, 'cover');
    }

    /* -------------------------------------------------------
     |  URL DINAMICO (CF Transformations) con fallback locale
     * ------------------------------------------------------*/
    public function imageUrl(?int $w = null, ?int $h = null, int $q = self::IMG_QUALITY, string $fit = 'cover'): ?string
    {
        if (!$this->image_path) return null;

        $origin = Storage::disk('public')->url($this->image_path); // /storage/...
        $path   = ltrim(parse_url($origin, PHP_URL_PATH), '/');

        if ($this->useCloudflareImage()) {
            $ops = ["format=auto", "quality={$q}", "fit={$fit}"];
            if ($w) $ops[] = "width={$w}";
            if ($h) $ops[] = "height={$h}";
            return '/cdn-cgi/image/' . implode(',', $ops) . '/' . $path;
        }

        // Fallback locale → preferisci .webp se presente
        return $this->preferWebp($this->image_path);
    }

    /* -------------------------------------------------------
     |  SRCSET GENERICO
     * ------------------------------------------------------*/
    public function imageSrcset(array $widths = self::CARD_WIDTHS, int $q = self::IMG_QUALITY, string $fit = 'cover'): ?string
    {
        if (!$this->image_path) return null;
        $parts = [];
        foreach ($widths as $w) {
            $parts[] = $this->imageUrl($w, null, $q, $fit) . " {$w}w";
        }
        return implode(', ', $parts);
    }

    /* -------------------------------------------------------
     |  PRESET PRONTI: CARD e DETAIL
     * ------------------------------------------------------*/
    public function cardSrc(int $w = 480): ?string
    {
        return $this->imageUrl($w, null, static::IMG_QUALITY, 'cover');
    }

    public function cardSrcset(): ?string
    {
        return $this->imageSrcset(static::CARD_WIDTHS, static::IMG_QUALITY, 'cover');
    }

    public function detailSrc(int $w = 1200): ?string
    {
        return $this->imageUrl($w, null, static::IMG_QUALITY, 'cover');
    }

    public function detailSrcset(): ?string
    {
        return $this->imageSrcset(static::DETAIL_WIDTHS, static::IMG_QUALITY, 'cover');
    }

    /* -------------------- Relations ---------------------- */
    public function scopePublished($q){ return $q->where('status','published'); }
    public function category(){ return $this->belongsTo(Category::class); }
    public function builder(){ return $this->belongsTo(Builder::class); }

    public function tutorials()
    {
        return $this->morphMany(\App\Models\Tutorial::class, 'tutorialable')
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    /* -------------------- Helpers interni ---------------- */
    private function useCloudflareImage(): bool
    {
        return (bool) (config('cdn.use_cloudflare', env('USE_CF_IMAGE', true)));
    }

    private function preferWebp(?string $path): ?string
    {
        if (!$path) return null;
        $disk = Storage::disk('public');
        $webp = preg_replace('/\.(jpe?g|png|gif|bmp)$/i', '.webp', $path);
        if ($webp && $disk->exists($webp)) {
            return $disk->url($webp);
        }
        return $disk->url($path);
    }
}