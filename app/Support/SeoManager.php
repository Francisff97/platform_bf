<?php

namespace App\Support;

use App\Models\SeoPage;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

class SeoManager
{
    public static function enabled(): bool
    {
        return (bool) config('seo.enabled', true);
    }

    /**
     * Ritorna meta già compilati con le variabili di $ctx.
     */
    public static function pageMeta(?string $route = null, ?string $path = null, array $ctx = []): array
    {
        if (!static::enabled()) {
            return ['title'=>null,'description'=>null,'og_image'=>null];
        }

        $route = $route ?? optional(Route::current())->getName();
        $path  = $path  ?? '/'.ltrim(request()->path(), '/');

        $page = SeoPage::query()
            ->when($route, fn($q) => $q->where('route_name',$route))
            ->when(!$route && $path, fn($q) => $q->orWhere('path',$path))
            ->first();

        $rawTitle = $page?->meta_title;
        $rawDesc  = $page?->meta_description;
        $rawImg   = $page?->og_image_path;

        $imgUrl = null;
        if ($rawImg) {
            $imgUrl = Str::startsWith($rawImg, ['http://','https://'])
                ? $rawImg
                : Storage::disk('public')->url($rawImg);
        }

        $base = [
            'app_name' => config('app.name'),
            'url'      => url()->current(),
        ];

        $vars = array_merge($base, $ctx, [
            'image_url' => $ctx['image_url'] ?? $imgUrl,
        ]);

        return [
            'title'       => static::compile($rawTitle, $vars),
            'description' => static::compile($rawDesc,  $vars),
            'og_image'    => $vars['image_url'] ?? null,
        ];
    }

    /**
     * Sostituisce {token} con i valori in $vars (supporta dot-notation).
     */
    public static function compile(?string $template, array $vars): ?string
    {
        if (!$template) return $template;

        return preg_replace_callback('/\{([a-z0-9_.-]+)\}/i', function ($m) use ($vars) {
            $key = $m[1];
            $val = Arr::get($vars, $key);
            if (is_null($val)) {
                $val = Arr::get($vars, str_replace('_','.', $key));
            }
            return is_scalar($val) ? (string) $val : $m[0];
        }, $template);
    }

    /**
     * Crea un contesto standard da un modello (Pack/Builder/Coach…)
     */
    public static function contextFromModel(object $model): array
    {
        $ctx = [];
        foreach (['id','name','title','slug','excerpt','description'] as $k) {
            if (isset($model->{$k})) $ctx[$k] = $model->{$k};
        }

        if (!empty($model->image_path)) {
            $ctx['image_url'] = Storage::disk('public')->url($model->image_path);
        }

        if (isset($model->price_cents)) {
            $currency          = $model->currency ?? 'EUR';
            $ctx['price_cents'] = (int) $model->price_cents;
            $ctx['currency']    = $currency;
            $ctx['price_eur']   = number_format($model->price_cents / 100, 2).' '.$currency;
        }

        if (method_exists($model, 'builder')) {
            $builder = $model->builder;
            if ($builder) {
                $ctx['builder'] = [
                    'name' => $builder->name ?? null,
                    'slug' => $builder->slug ?? null,
                ];
                $ctx['builder_name'] = $builder->name ?? null;
            }
        }

        return $ctx;
    }

    /* ============================
       🔽  Metodi ripristinati  🔽
       ============================ */

    /** Restituisce alt/lazy da tabella media_assets (se presente) a partire da una URL */
    public static function imgAttrsByUrl(string $url): array
    {
        $path = static::toStoragePath($url);
        if (!$path) return ['alt'=>null,'lazy'=>true];

        $m = class_exists(\App\Models\MediaAsset::class)
            ? \App\Models\MediaAsset::where('path',$path)->first()
            : null;

        // supporta sia alt che alt_text / is_lazy
        $alt  = $m?->alt ?? $m?->alt_text ?? null;
        $lazy = $m?->is_lazy ?? true;

        return ['alt'=>$alt, 'lazy'=>$lazy];
    }

    /** Converte una URL /storage/... o assoluta -> path relativo sul disk ('public') */
    public static function toStoragePath(string $url): ?string
    {
        if (Str::startsWith($url, ['http://','https://'])) {
            $p = parse_url($url, PHP_URL_PATH) ?? '';
            if (Str::contains($p, '/storage/')) {
                return ltrim(Str::after($p, '/storage/'), '/');
            }
            return null;
        }
        if (Str::startsWith($url, '/storage/')) {
            return ltrim(Str::after($url, '/storage/'), '/');
        }
        return ltrim($url, '/');
    }

    /** Opzionale: arricchisce <img> in HTML con alt/lazy dal catalogo */
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

            $lazy = $attrs['lazy'] ?? true;
            if ($lazy) {
                if (preg_match('/\sloading=["\']/i', $tag)) {
                    $tag = preg_replace('/loading=["\'][^"\']*["\']/', 'loading="lazy"', $tag);
                } else {
                    $tag = rtrim($tag, '>'); $tag .= ' loading="lazy">';
                }
            }

            return $tag;
        }, $html);
    }
}
