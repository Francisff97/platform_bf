<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Storage;
use App\Traits\ConvertsToWebp;

class Pack extends Model
{
    use HasFactory, ConvertsToWebp;

    /** Qualità/fit e cap di larghezza (per schermi enormi) */
    private const IMG_Q     = 82;
    private const IMG_FIT   = 'cover';   // 'cover' oppure 'contain'
    private const IMG_W_MAX = 1920;      // limite superiore facoltativo

    protected $fillable = [
        'title','slug','excerpt','description',
        'image_path',
        'price_cents','currency',
        'is_featured','status','published_at',
        'category_id','builder_id',
    ];

    protected $casts = [
        'is_featured'  => 'boolean',
        'published_at' => 'datetime',
    ];

    protected static function booted()
    {
        // Manteniamo il WebP locale come fallback
        static::saved(function (self $m) {
            if ($m->image_path) {
                $m->toWebp('public', $m->image_path, 75);
            }
        });
    }

    /**
     * Blade continua ad usare:  <img src="{{ $pack->image_url }}">
     * Con Cloudflare:
     *   /cdn-cgi/image/width=auto:1920,dpr=auto,fit=cover,format=auto,quality=82/<path>
     * -> ridimensionamento automatico in base a DPR/viewport (client hints)
     */
    public function getImageUrlAttribute(): ?string
{
    if (!$this->image_path) return null;

    // origine locale
    $origin = \Storage::disk('public')->url($this->image_path);
    $path   = ltrim(parse_url($origin, PHP_URL_PATH), '/');

    if (config('cdn.use_cloudflare', env('USE_CF_IMAGE', true))) {
        // width NUMERICO (niente auto/dpr)
        $ops = ['format=auto', 'quality=82', 'fit=cover', 'width=800'];
        return '/cdn-cgi/image/'.implode(',', $ops).'/'.$path;
    }

    // fallback webp locale (come avevi)
    $webp = preg_replace('/\.(jpe?g|png|gif|bmp)$/i', '.webp', $this->image_path);
    if ($webp && \Storage::disk('public')->exists($webp)) {
        return \Storage::disk('public')->url($webp);
    }
    return \Storage::disk('public')->url($this->image_path);
}

    /* ================= Scopes & Relations (evitano gli errori visti) ================ */

    /** scopePublished() usato nei controller */
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    /** Relazioni usate da with(['category','builder']) */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function builder()
    {
        return $this->belongsTo(Builder::class);
    }

    /** Se ti serve (come nel codice che avevi inviato) */
    public function tutorials()
    {
        return $this->morphMany(\App\Models\Tutorial::class, 'tutorialable')
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    /* ============================= Helpers interni ============================= */

    private function useCloudflareImage(): bool
    {
        // abilita con USE_CF_IMAGE=true o config('cdn.use_cloudflare')
        return (bool) (config('cdn.use_cloudflare', env('USE_CF_IMAGE', true)));
    }

    /** Costruisce l’URL /cdn-cgi/image/.../<path from disk> */
    private function cdnFromDisk(string $diskPath, array $ops): string
    {
        $origin = Storage::disk('public')->url($diskPath);        // es: /storage/...
        $path   = ltrim(parse_url($origin, PHP_URL_PATH), '/');    // rimuove lo slash iniziale
        return '/cdn-cgi/image/' . implode(',', $ops) . '/' . $path;
    }

    /** Preferisci WebP locale se esiste, altrimenti l’originale */
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