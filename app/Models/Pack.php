<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Storage;
use App\Traits\ConvertsToWebp;

class Pack extends Model
{
    use HasFactory, ConvertsToWebp;

    // qualità/fit default per tutte le immagini dei pack
    private const IMG_Q   = 82;
    private const IMG_FIT = 'cover'; // o 'contain' se preferisci

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
                // manteniamo il tuo WebP locale come fallback
                $m->toWebp('public', $m->image_path, 75);
            }
        });
    }

    /** Blade continua a usare:  <img src="{{ $pack->image_url }}"> */
    public function getImageUrlAttribute(): ?string
    {
        if (!$this->image_path) return null;

        // URL origine (es. /storage/…)
        $origin = Storage::disk('public')->url($this->image_path);
        $path   = ltrim(parse_url($origin, PHP_URL_PATH), '/');

        if ($this->useCloudflareImage()) {
            // **Nessuna modifica alle Blade**: un'unica URL che si adatta
            // - width=auto  → Cloudflare sceglie la larghezza in base a Client Hints
            // - dpr=auto    → densità pixel corretta (retina, ecc.)
            // - format=auto → avif/webp/jpg in base al browser
            // - quality     → qualità target
            // - fit         → come ritagliare/scalare
            $ops = [
                'width=auto',
                'dpr=auto',
                'format=auto',
                'quality='.self::IMG_Q,
                'fit='.self::IMG_FIT,
            ];
            return '/cdn-cgi/image/'.implode(',', $ops).'/'.$path;
        }

        // Fallback locale (se disabiliti CF)
        return $this->preferWebp($this->image_path);
    }

    /* ----------------- helpers interni ----------------- */

    private function useCloudflareImage(): bool
    {
        // metti USE_CF_IMAGE=true in .env oppure config('cdn.use_cloudflare')
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