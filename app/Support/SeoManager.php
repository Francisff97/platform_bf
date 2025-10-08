<?php

namespace App\Support;

use App\Models\SeoPage;
use App\Models\MediaAsset;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class SeoManager
{
    public static function enabled(): bool
    {
        return (bool) config('seo.enabled', true);
    }

    public static function pageMeta(?string $route = null, ?string $path = null): array
    {
        if (!static::enabled()) return ['title'=>null,'description'=>null,'og_image'=>null];

        $route = $route ?? optional(Route::current())->getName();
        $path  = $path  ?? '/'.ltrim(request()->path(), '/');

        $page = SeoPage::query()
            ->when($route, fn($q)=>$q->where('route_name',$route))
            ->when(!$route && $path, fn($q)=>$q->orWhere('path',$path))
            ->first();

        return [
            'title'       => $page?->meta_title,
            'description' => $page?->meta_description,
            'og_image'    => $page?->og_image_path
                ? (Str::startsWith($page->og_image_path, ['http://','https://'])
                    ? $page->og_image_path
                    : Storage::disk('public')->url($page->og_image_path))
                : null,
        ];
    }

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
}
