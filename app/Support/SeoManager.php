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
     * Recupera i meta per route/path e compila i placeholder con $ctx.
     * Esempi placeholder: {name}, {title}, {slug}, {builder_name}, {price}, {image_url}...
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

        // Normalizza contesto (aggiunge alias e fallback utili)
        $vars = static::normalizeContext($ctx);

        // Compila titoli/descrizioni con placeholder
        $title = static::compile($page?->meta_title, $vars);
        $desc  = static::compile($page?->meta_description, $vars);

        // Risolvi immagine OG: 1) campo su tabella  2) image_url dal ctx  3) null
        $og = null;
        if ($page?->og_image_path) {
            $og = Str::startsWith($page->og_image_path, ['http://','https://'])
                ? $page->og_image_path
                : Storage::disk('public')->url(ltrim($page->og_image_path,'/'));
        } elseif (!empty($vars['image_url'])) {
            $og = $vars['image_url'];
        }

        return [
            'title'       => $title,
            'description' => $desc,
            'og_image'    => $og,
        ];
    }

    /**
     * Compilatore placeholder molto permissivo: sostituisce {key} con $vars[key] (string),
     * ignora le chiavi mancanti (toglie le graffe).
     */
    public static function compile(?string $tpl, array $vars): ?string
    {
        if ($tpl === null || $tpl === '') return $tpl;

        // {price_cents} ecc. -> cast a stringa
        $replace = [];
        foreach ($vars as $k => $v) {
            if (is_scalar($v))        $replace['{'.$k.'}'] = (string) $v;
            elseif (method_exists($v, '__toString')) $replace['{'.$k.'}'] = (string) $v;
        }
        $out = strtr($tpl, $replace);

        // Pulisci placeholder rimasti (chiavi mancanti)
        $out = preg_replace('/\{[a-z0-9_\.\-]+\}/i', '', $out);
        // Spazi multipli dopo pulizia
        return trim(preg_replace('/\s{2,}/', ' ', $out));
    }

    /**
     * Normalizza il contesto per Pack/Builder/Coach o generico Eloquent Model.
     * - name/title fallback
     * - slug
     * - description/excerpt
     * - image_url (da molti possibili campi)
     * - builder_name (se relazione esiste)
     */
    public static function normalizeContext(array $ctx): array
    {
        $out = $ctx;

        // Se arriva un modello, prendi gli attributi
        if (isset($ctx['_model']) && is_object($ctx['_model'])) {
            $m   = $ctx['_model'];
            $arr = method_exists($m, 'toArray') ? $m->toArray() : [];

            // name/title
            $name  = $arr['name']  ?? $arr['title'] ?? $arr['slug'] ?? null;
            $title = $arr['title'] ?? $arr['name']  ?? null;
            $slug  = $arr['slug']  ?? null;

            // description
            $desc  = $arr['meta_description'] ?? $arr['description'] ?? $arr['excerpt'] ?? null;

            // image path candidates
            $imgPath = $arr['og_image_path']
                ?? $arr['image_path']
                ?? $arr['cover_path']
                ?? $arr['thumbnail_path']
                ?? null;

            // builder name se relazione esiste
            $builderName = null;
            if (method_exists($m, 'builder')) {
                try {
                    $builder = $m->builder;
                    $builderName = $builder?->name ?? $builder?->title ?? null;
                } catch (\Throwable $e) {}
            }

            // absolute image url
            $imageUrl = null;
            if ($imgPath) {
                $imageUrl = Str::startsWith($imgPath, ['http://','https://'])
                    ? $imgPath
                    : Storage::disk('public')->url(ltrim($imgPath,'/'));
            }

            $out = array_merge([
                'name'         => $name,
                'title'        => $title ?? $name,
                'slug'         => $slug,
                'description'  => $desc,
                'builder_name' => $builderName,
                'image_url'    => $imageUrl,
            ], $out);
        }

        // Coerenza alias espliciti se passati manualmente
        if (!empty($out['title']) && empty($out['name']))  $out['name']  = $out['title'];
        if (!empty($out['name'])  && empty($out['title'])) $out['title'] = $out['name'];

        return $out;
    }

    /**
     * Alt + lazy per immagini (usato dai componenti).
     */
    public static function imgAttrsByUrl(string $url): array
    {
        if (!static::enabled()) return ['alt'=>null,'lazy'=>true];

        $path = static::toStoragePath($url);
        $m = $path ? MediaAsset::where('path',$path)->first() : null;

        return [
            'alt'  => $m?->alt ?? $m?->alt_text ?? null,
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

    /**
     * Helper per creare un contesto standard da un modello.
     */
    public static function contextFromModel($model): array
    {
        return ['_model' => $model];
    }
}
