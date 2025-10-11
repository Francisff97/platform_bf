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
     * Ritorna meta già **compilati** con le variabili di $ctx.
     * - $ctx può contenere qualsiasi chiave (es. name, slug, price_eur, image_url…)
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

        // Valori grezzi dal DB
        $rawTitle = $page?->meta_title;
        $rawDesc  = $page?->meta_description;
        $rawImg   = $page?->og_image_path;

        // Normalizza immagine in URL assoluto (se presente)
        $imgUrl = null;
        if ($rawImg) {
            $imgUrl = Str::startsWith($rawImg, ['http://','https://'])
                ? $rawImg
                : Storage::disk('public')->url($rawImg);
        }

        // Aggiungo sempre qualche variabile base disponibili ovunque
        $base = [
            'app_name' => config('app.name'),
            'url'      => url()->current(),
        ];

        $vars = array_merge($base, $ctx, [
            'image_url' => $ctx['image_url'] ?? $imgUrl, // preferisci ctx, altrimenti dal DB
        ]);

        return [
            'title'       => static::compile($rawTitle, $vars),
            'description' => static::compile($rawDesc,  $vars),
            'og_image'    => $vars['image_url'] ?? null,
        ];
    }

    /**
     * Compila stringhe sostituendo {token} con i valori in $vars.
     * Esempio: "Buy {name} – {price_eur}".
     */
    public static function compile(?string $template, array $vars): ?string
    {
        if (!$template) return $template;

        // Supporta sia {key} sia {dot.path} (es {builder.name})
        return preg_replace_callback('/\{([a-z0-9_.-]+)\}/i', function ($m) use ($vars) {
            $key = $m[1];
            // Prova dot-notation
            $val = Arr::get($vars, $key);
            if (is_null($val)) {
                // fallback: prova anche con underscore-to-dot (es: builder_name)
                $val = Arr::get($vars, str_replace('_','.', $key));
            }
            return is_scalar($val) ? (string) $val : $m[0];
        }, $template);
    }

    /**
     * Crea un contesto standard da un modello (Pack/Builder/Coach…)
     * Puoi estendere i campi a piacere.
     */
    public static function contextFromModel(object $model): array
    {
        $ctx = [];

        // Campi comuni se esistono
        foreach (['id','name','title','slug','excerpt','description'] as $k) {
            if (isset($model->{$k})) $ctx[$k] = $model->{$k};
        }

        // Immagine (se memorizzata come image_path in disk 'public')
        if (!empty($model->image_path)) {
            $ctx['image_url'] = Storage::disk('public')->url($model->image_path);
        }

        // Pack: prezzi & builder
        if (isset($model->price_cents)) {
            $currency = $model->currency ?? 'EUR';
            $ctx['price_cents'] = (int) $model->price_cents;
            $ctx['currency']    = $currency;
            $ctx['price_eur']   = number_format($model->price_cents / 100, 2).' '.$currency;
        }
        if (method_exists($model, 'builder') && $model->relationLoaded('builder') || method_exists($model, 'builder')) {
            $builder = $model->builder ?? null;
            if ($builder) {
                $ctx['builder'] = [
                    'name' => $builder->name ?? null,
                    'slug' => $builder->slug ?? null,
                ];
                // comodi alias piatti
                $ctx['builder_name'] = $builder->name ?? null;
            }
        }

        return $ctx;
    }
}
