<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Storage;

use App\Traits\ConvertsToWebp;

class Builder extends Model
{
    use HasFactory, ConvertsToWebp;

    /** Qualità/fit predefiniti per i preset */
    private const IMG_Q   = 82;
    private const IMG_FIT = 'cover';

    protected $fillable = ['name','slug','team','image_path','skills','description'];
    protected $casts    = ['skills' => 'array'];

    protected static function booted()
    {
        static::saved(function (self $m) {
            if ($m->image_path) {
                // fallback WebP locale per quando Cloudflare Image Resize è disattivo
                $m->toWebp('public', $m->image_path, 75);
            }
        });
    }

    /** Accessor compatto (senza dimensioni) */
    public function getImageUrlAttribute(): ?string
    {
        return $this->imageUrl(); // nessuna dimensione → solo ottimizzazioni
    }

    /**
     * URL immagine con dimensioni opzionali.
     * - Se CF è OFF: ritorna Storage::url(...) preferendo eventuale .webp
     * - Se CF è ON: costruisce URL assoluto same-origin con /cdn-cgi/image/...
     */
    public function imageUrl(?int $w = null, ?int $h = null, int $q = self::IMG_Q, string $fit = self::IMG_FIT): ?string
    {
        if (!$this->image_path) return null;

        // URL origin (funziona sempre)
        $origin = Storage::disk('public')->url($this->image_path);

        // CF disattivo → usa preferWebp (Storage diretto)
        if (!$this->useCloudflareImage()) {
            return $this->preferWebp($this->image_path);
        }

        // CF attivo → genera URL assoluto sullo stesso host per evitare mismatch
        $ops = ['format=auto', "quality={$q}", "fit={$fit}"];
        if ($w) $ops[] = "width={$w}";
        if ($h) $ops[] = "height={$h}";

        // Estraggo il path relativo servito da /storage/...
        $pathOnly = ltrim(parse_url($origin, PHP_URL_PATH) ?: '', '/');

        // Base host (preferisci request; fallback app.url)
        $base = rtrim($this->baseUrl(), '/');

        return "{$base}/cdn-cgi/image/".implode(',', $ops)."/{$pathOnly}";
    }

    /** ===== Preset comodi per le Blade ===== */

    // Card lista builders (circa 720x300 su desktop)
    public function gridSrc(): ?string
    {
        return $this->imageUrl(720, 300);
    }

    // Dettaglio (4:3)
    public function showSrc(): ?string
    {
        return $this->imageUrl(1200, 900);
    }

    // Avatar tondo
    public function avatarSrc(int $size = 224): ?string
    {
        return $this->imageUrl($size, $size, self::IMG_Q, 'cover');
    }

    /** ===== Relazioni ===== */
    public function packs()
    {
        return $this->hasMany(Pack::class);
    }

    /** ===== Helpers interni ===== */

    /**
     * Legge la config cacheabile; fallback all'env.
     * Imposta USE_CF_IMAGE=false in .env se vuoi forzare lo spegnimento.
     */
    private function useCloudflareImage(): bool
    {
        // config/cdn.php → ['use_cloudflare' => env('USE_CF_IMAGE', false)]
        return (bool) config('cdn.use_cloudflare', (bool) env('USE_CF_IMAGE', false));
    }

    /** Preferisce il .webp locale se presente, altrimenti l’originale */
    private function preferWebp(?string $path): ?string
    {
        if (!$path) return null;
        $disk = Storage::disk('public');
        $webp = preg_replace('/\.(jpe?g|png|gif|bmp)$/i', '.webp', $path);
        return ($webp && $disk->exists($webp)) ? $disk->url($webp) : $disk->url($path);
    }

    /** Base URL dell’app (request → app.url) */
    private function baseUrl(): string
    {
        // In CLI / queue potrebbe non esserci la request
        $fromRequest = request()?->getSchemeAndHttpHost();
        if (!empty($fromRequest)) return $fromRequest;

        // Fallback impostato in config/app.php ('url' => env('APP_URL', 'http://localhost'))
        return rtrim((string) config('app.url', ''), '/');
    }
}