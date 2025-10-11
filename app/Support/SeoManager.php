<?php

namespace App\Support;

use App\Models\SeoPage;
use App\Models\MediaAsset;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SeoManager
{
    public static function enabled(): bool
    {
        return (bool) config('seo.enabled', true);
    }

    /**
     * Restituisce i meta per la pagina corrente (o per route/path passati) con
     * SUPPORTO VARIABILI: passa $ctx = ['name'=>'...','slug'=>'...'] ecc.
     */
    public static function pageMeta(?string $route = null, ?string $path = null, array $ctx = []): array
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

        // leggi raw
        $rawTitle = $page?->meta_title ?: null;
        $rawDesc  = $page?->meta_description ?: null;
        $rawOg    = $page?->og_image_path ?: null;

        // Interpolazione {var}
        $title = static::interpolate($rawTitle, $ctx);
        $desc  = static::interpolate($rawDesc,  $ctx);
        $og    = static::interpolate($rawOg,    $ctx);

        // Normalizza og_image → assoluto
        $og = $og
            ? (Str::startsWith($og, ['http://','https://'])
                ? $og
                : Storage::disk('public')->url(ltrim($og,'/')))
            : null;

        return [
            'title'       => $title,
            'description' => $desc,
            'og_image'    => $og,
        ];
    }

    /** Sostituisce i token {chiave} in modo case-insensitive. */
    public static function interpolate(?string $text, array $ctx): ?string
    {
        if (!$text || empty($ctx)) return $text;

        // normalizza chiavi: {name}, {slug}, {price_eur}, ecc.
        $replacements = [];
        foreach ($ctx as $k => $v) {
            $replacements['{'.strtolower($k).'}'] = (string) $v;
        }

        // sostituzione case-insensitive tenendo {chiave} come pattern
        return preg_replace_callback('/\{([a-z0-9_\.]+)\}/i', function($m) use ($replacements){
            $key = '{'.strtolower($m[1]).'}';
            return array_key_exists($key, $replacements) ? $replacements[$key] : $m[0];
        }, $text);
    }

    /**
     * Crea un contesto standard da un Model (Pack/Builder/Coach/Service).
     * Puoi passarlo alle view -> $seoCtx = SeoManager::contextFromModel($model).
     */
    public static function contextFromModel($model): array
    {
        if (!$model) return [];

        // base
        $ctx = [
            'id'          => $model->id ?? null,
            'name'        => $model->name ?? ($model->title ?? null),
            'title'       => $model->title ?? ($model->name ?? null),
            'slug'        => $model->slug ?? null,
            'excerpt'     => $model->excerpt ?? null,
            'description' => $model->description ?? ($model->body ?? null),
            'image_url'   => null,
            'builder_name'=> method_exists($model,'builder') ? optional($model->builder)->name : null,
            'team'        => $model->team ?? null,
            'price_eur'   => null,
            'currency'    => $model->currency ?? null,
        ];

        // immagine
        $imagePath = $model->image_path ?? null;
        if ($imagePath) {
            $ctx['image_url'] = Storage::disk('public')->url($imagePath);
        }

        // prezzo (se presenti price_cents/currency)
        if (isset($model->price_cents)) {
            $curr = $model->currency ?? 'EUR';
            $ctx['price_eur'] = number_format($model->price_cents/100, 2, ',', '.').' '.$curr;
        }

        // pulizia null -> stringhe
        return array_filter($ctx, fn($v) => !is_null($v));
    }

    // ---- Meta per immagini inline nei contenuti (come già avevi) ----
    public static function imgAttrsByUrl(string $url): array
    {
        if (!static::enabled()) return ['alt'=>null,'lazy'=>true];
        $path = static::toStoragePath($url);
        $m = $path ? MediaAsset::where('path',$path)->first() : null;
        return ['alt'=>$m?->alt_text, 'lazy'=>$m?->is_lazy ?? true];
    }

    public static function toStoragePath(string $url): ?string
    {
        if (Str::startsWith($url, ['http://','https://'])) {
            $p = parse_url($url, PHP_URL_PATH) ?? '';
            if (Str::contains($p, '/storage/')) return ltrim(Str::after($p, '/storage/'), '/');
            return null;
        }
        if (Str::startsWith($url, '/storage/')) return ltrim(Str::after($url, '/storage/'), '/');
        return ltrim($url, '/');
    }

    public static function enrichHtmlImages(string $html): string
    {
        if (!static::enabled()) return $html;

        return preg_replace_callback('#<img\s+[^>]*src=["\']([^"\']+)["\'][^>]*>#i', function($m){
            $src   = $m[1];
            $attrs = static::imgAttrsByUrl($src);
            $tag   = $m[0];

            if (($attrs['alt'] ?? null) !== null) {
                $tag = preg_replace('/\salt=["\'][^"\']*["\']/', ' alt="'.e($attrs['alt']).'"', $tag) ?? rtrim($tag,'>').' alt="'.e($attrs['alt']).'">';
            }
            if ($attrs['lazy'] ?? true) {
                $tag = preg_replace('/\sloading=["\'][^"\']*["\']/', ' loading="lazy"', $tag) ?? rtrim($tag,'>').' loading="lazy">';
            }
            return $tag;
        }, $html);
    }

    /**
     * (Opzionale) Stampa direttamente i meta nel <head>.
     */
    public static function renderHead(?array $ctx = null): string
    {
        $meta = static::pageMeta(null, null, $ctx ?? []);
        $title = e($meta['title'] ?? config('app.name'));
        $desc  = e($meta['description'] ?? '');
        $og    = $meta['og_image'] ? e($meta['og_image']) : '';

        return <<<HTML
<title>{$title}</title>
<meta name="description" content="{$desc}">
<meta property="og:title" content="{$title}">
<meta property="og:description" content="{$desc}">
<meta property="og:type" content="website">
HTML
.($og ? "\n<meta property=\"og:image\" content=\"{$og}\">" : '');
    }
}
