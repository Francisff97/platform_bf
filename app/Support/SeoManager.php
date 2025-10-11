<?php

namespace App\Support;

use App\Models\SeoPage;
use App\Models\MediaAsset;
use App\Models\Pack;
use App\Models\Builder;
use App\Models\Coach;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SeoManager
{
    /** Abilita/disabilita SEO manager via config('seo.enabled') */
    public static function enabled(): bool
    {
        return (bool) config('seo.enabled', true);
    }

    /**
     * Recupera i meta per la pagina corrente e compila i placeholder
     * (supporta {var}, {{var}} e (var)).
     */
    public static function pageMeta(?string $route = null, ?string $path = null): array
    {
        if (!static::enabled()) {
            return ['title'=>null,'description'=>null,'og_image'=>null];
        }

        $route ??= optional(Route::current())->getName();
        $path  ??= '/'.ltrim(request()->path(), '/');

        $page = SeoPage::query()
            ->when($route, fn($q) => $q->where('route_name', $route))
            ->when(!$route && $path, fn($q) => $q->orWhere('path', $path))
            ->first();

        // Costruiamo il CONTEXT automaticamente dai parametri di rotta (per le *show*)
        $ctx = static::autoContextFromRoute();

        // Titolo & description compilati
        $title = static::compileTemplate($page?->meta_title, $ctx);
        $desc  = static::compileTemplate($page?->meta_description, $ctx);

        // OG image: se il record ha un percorso immagine con placeholder, compilalo.
        $ogFromPage = static::compileTemplate($page?->og_image_path, $ctx);
        $ogUrl = static::resolveImageUrl($ogFromPage ?: ($ctx['image_url'] ?? null));

        return [
            'title'       => $title,
            'description' => $desc,
            'og_image'    => $ogUrl,
        ];
    }

    /**
     * Compila una stringa sostituendo {var}, {{var}} e (var) con i valori presenti in $ctx.
     */
    public static function compileTemplate(?string $tpl, array $ctx): ?string
    {
        if (!$tpl) return $tpl;

        // Normalizza chiavi (case-insensitive)
        $norm = [];
        foreach ($ctx as $k => $v) {
            $norm[strtolower($k)] = $v;
        }

        // Funzione sostituzione
        $replace = function(string $text) use ($norm) {
            // {foo}
            $text = preg_replace_callback('/\{([a-z0-9_\.]+)\}/i', function($m) use ($norm){
                $key = strtolower($m[1]);
                return array_key_exists($key, $norm) ? (string) $norm[$key] : $m[0];
            }, $text);

            // {{foo}}
            $text = preg_replace_callback('/\{\{\s*([a-z0-9_\.]+)\s*\}\}/i', function($m) use ($norm){
                $key = strtolower($m[1]);
                return array_key_exists($key, $norm) ? (string) $norm[$key] : $m[0];
            }, $text);

            // (foo)
            $text = preg_replace_callback('/\(([a-z0-9_\.]+)\)/i', function($m) use ($norm){
                $key = strtolower($m[1]);
                return array_key_exists($key, $norm) ? (string) $norm[$key] : $m[0];
            }, $text);

            return $text;
        };

        return $replace($tpl);
    }

    /**
     * Prova a capire il contesto da rotta: packs.show/builders.show/coaches.show
     * Restituisce: ['name'=>..., 'title'=>..., 'slug'=>..., 'image_url'=>..., ...]
     */
    public static function autoContextFromRoute(): array
    {
        $ctx = [];
        $routeName = optional(Route::current())->getName();
        $params = request()->route()?->parameters() ?? [];

        // Helper per preparare il contesto comune
        $fillFromModel = function($m) use (&$ctx){
            if (!$m) return;
            $ctx['name']      = $m->name       ?? $m->title ?? null;
            $ctx['title']     = $m->title      ?? $m->name  ?? null;
            $ctx['slug']      = $m->slug       ?? null;
            $ctx['image_url'] = isset($m->image_path) ? Storage::disk('public')->url($m->image_path) : null;
            // Se hai altri campi utili ai template, aggiungili qui.
        };

        try {
            if ($routeName === 'packs.show' && isset($params['slug'])) {
                $fillFromModel(Pack::where('status','published')->where('slug',$params['slug'])->first());
            } elseif ($routeName === 'builders.show' && isset($params['slug'])) {
                $fillFromModel(Builder::where('slug',$params['slug'])->first());
            } elseif ($routeName === 'coaches.show' && isset($params['slug'])) {
                $fillFromModel(Coach::where('slug',$params['slug'])->first());
            }
        } catch (\Throwable $e) {
            // no-op: non bloccare se i modelli non esistono in questo progetto
        }

        // Aggiungi anche i parametri "grezzi" (slug ecc.) così sono sempre disponibili
        foreach ($params as $k => $v) {
            if (is_scalar($v)) $ctx[$k] = $v;
        }

        return $ctx;
    }

    /** Converte un path relativo del disk 'public' in URL assoluto. */
    protected static function resolveImageUrl(?string $maybePath): ?string
    {
        if (!$maybePath) return null;

        if (Str::startsWith($maybePath, ['http://','https://'])) {
            return $maybePath;
        }

        // consentiamo che nel DB sia salvato già /storage/....
        if (Str::startsWith($maybePath, '/storage/')) {
            return url($maybePath);
        }

        // altrimenti assumiamo path relativo sul disk public
        return Storage::disk('public')->url(ltrim($maybePath,'/'));
    }

    /** Arricchisce <img> nel markup con alt/lazy provenienti da media_assets (opzionale) */
    public static function imgAttrsByUrl(string $url): array
    {
        if (!static::enabled()) return ['alt'=>null,'lazy'=>true];

        $path = static::toStoragePath($url);
        $m = $path ? MediaAsset::where('path',$path)->first() : null;

        return [
            'alt'  => $m?->alt_text,
            'lazy' => $m?->is_lazy ?? true,
        ];
    }

    /** Estrae il path relativo da una URL /storage/... o assoluta verso /storage/... */
    public static function toStoragePath(string $url): ?string
    {
        if (Str::startsWith($url, ['http://','https://'])) {
            $p = parse_url($url, PHP_URL_PATH) ?? '';
            return Str::contains($p, '/storage/') ? ltrim(Str::after($p, '/storage/'), '/') : null;
        }
        return Str::startsWith($url, '/storage/')
            ? ltrim(Str::after($url, '/storage/'), '/')
            : ltrim($url, '/');
    }
}
