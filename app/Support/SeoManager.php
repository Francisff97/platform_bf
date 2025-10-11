<?php
namespace App\Support;

use App\Models\SeoPage;
use App\Models\MediaAsset;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Contracts\Support\Arrayable;

class SeoManager
{
    public static function enabled(): bool
    {
        return (bool) config('seo.enabled', true);
    }

    /**
     * Ritorna meta risolti (applicando i placeholder) per la pagina corrente
     * Esempio placeholder: {name}, {slug}, {excerpt}, {description}, {image_url}, {price_eur}, {builder_name}
     */
    public static function pageMeta(?string $route = null, ?string $path = null, mixed $subject = null): array
    {
        if (!static::enabled()) {
            return ['title'=>null,'description'=>null,'og_image'=>null];
        }

        $route = $route ?? optional(Route::current())->getName();
        $path  = $path  ?? '/'.ltrim(request()->path(), '/');

        $page = SeoPage::query()
            ->when($route, fn($q)=>$q->where('route_name',$route))
            ->when(!$route && $path, fn($q)=>$q->orWhere('path',$path))
            ->first();

        // Se non trovato, ritorna vuoto (non forziamo fallback qui)
        if (!$page) {
            return ['title'=>null,'description'=>null,'og_image'=>null];
        }

        $map = static::subjectMap($subject);

        $title = static::applyTemplate($page->meta_title ?? '', $map, 70);
        $desc  = static::applyTemplate($page->meta_description ?? '', $map, 160);
        $img   = static::applyTemplate($page->og_image_path ?? '', $map);

        // Se og_image è percorso relativo o /storage/... → rendi absolute
        $img = static::absolute($img);

        return [
            'title'       => $title ?: null,
            'description' => $desc ?: null,
            'og_image'    => $img ?: null,
        ];
    }

    /** Enrich <img> in HTML con alt/lazy presi da media_assets (rimane invariata) */
    public static function enrichHtmlImages(string $html): string
    {
        if (!static::enabled()) return $html;

        return preg_replace_callback('#<img\s+[^>]*src=["\']([^"\']+)["\'][^>]*>#i', function($m){
            $src   = $m[1];
            $attrs = static::imgAttrsByUrl($src);
            $tag   = $m[0];

            if (($attrs['alt'] ?? null) !== null) {
                if (preg_match('/\salt=["\']/i', $tag)) {
                    $tag = preg_replace('/alt=["\'][^"\']*["\']/', 'alt="'.e($attrs['alt']).'"', $tag);
                } else {
                    $tag = rtrim($tag, '>'); $tag .= ' alt="'.e($attrs['alt']).'">';
                }
            }

            if ($attrs['lazy'] ?? true) {
                if (preg_match('/\sloading=["\']/i', $tag)) {
                    $tag = preg_replace('/loading=["\'][^"\']*["\']/', 'loading="lazy"', $tag);
                } else {
                    $tag = rtrim($tag, '>'); $tag .= ' loading="lazy">';
                }
            }

            return $tag;
        }, $html);
    }

    public static function imgAttrsByUrl(string $url): array
    {
        if (!static::enabled()) return ['alt'=>null,'lazy'=>true];

        $path = static::toStoragePath($url);
        $m = $path ? \App\Models\MediaAsset::where('path',$path)->first() : null;
        return [
            'alt'  => $m?->alt_text,
            'lazy' => $m?->is_lazy ?? true,
        ];
    }

    public static function toStoragePath(string $url): ?string
    {
        if (Str::startsWith($url, ['http://','https://'])) {
            $parsed = parse_url($url, PHP_URL_PATH) ?? '';
            if (Str::contains($parsed, '/storage/')) {
                return ltrim(Str::after($parsed, '/storage/'), '/');
            }
            return null;
        }
        if (Str::startsWith($url, '/storage/')) {
            return ltrim(Str::after($url, '/storage/'), '/');
        }
        return ltrim($url, '/');
    }

    // ====== NEW: templating ======
    protected static function subjectMap($s): array
    {
        if (!$s) return [];

        $arr = $s instanceof Arrayable ? $s->toArray()
             : (is_object($s) ? get_object_vars($s) : (array) $s);

        // alias comuni
        $arr['name']        = $arr['name'] ?? $arr['title'] ?? null;
        $arr['slug']        = $arr['slug'] ?? null;
        $arr['excerpt']     = $arr['excerpt'] ?? null;
        $arr['description'] = $arr['description'] ?? null;

        // prezzo comodo
        if (isset($arr['price_cents'])) {
            $arr['price_eur'] = number_format(($arr['price_cents']/100), 2, ',', '.').' '.($arr['currency'] ?? 'EUR');
        }

        // image_url se c'è image_path
        if (!empty($arr['image_path'])) {
            $arr['image_url'] = Storage::url($arr['image_path']);
        }

        // builder_name se relazione disponibile
        if (is_object($s) && method_exists($s,'builder')) {
            try { $arr['builder_name'] = optional($s->builder)->name; } catch (\Throwable $e) {}
        }

        // normalizza a stringhe scalari
        return array_map(fn($v)=>is_scalar($v)?(string)$v:null, $arr);
    }

    /** sostituisce {token} con valori, compatta spazi, opz. tronca */
    protected static function applyTemplate(string $tpl, array $map, int $maxLen=0): string
    {
        if ($tpl === '') return '';
        $out = preg_replace_callback('/\{([a-z0-9_]+)\}/i', function($m) use ($map){
            $k = strtolower($m[1]);
            return $map[$k] ?? '';
        }, $tpl);
        $out = trim(preg_replace('/\s+/', ' ', $out));
        if ($maxLen > 0 && mb_strlen($out) > $maxLen) {
            $out = Str::limit($out, $maxLen, '…');
        }
        return $out;
    }

    protected static function absolute(?string $url): ?string
    {
        if (!$url) return null;
        if (Str::startsWith($url, ['http://','https://','data:'])) return $url;

        // se è "storage/foo.png" o "/storage/foo.png"
        if (Str::startsWith($url, '/storage/')) {
            return rtrim(config('app.url'), '/').$url;
        }
        if (!Str::startsWith($url, '/')) {
            // potrebbe essere path relativo su disk public
            $url = '/'.$url;
        }
        return rtrim(config('app.url'), '/').$url;
    }
}
