<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class Flags
{
    public static function get(): array
    {
        $slug = config('flags.slug');
        $fallback = config('flags.fallback');

        // Se manca lo slug o il base_url -> fallback
        if (!$slug || !config('flags.base_url')) {
            return $fallback;
        }

        $cacheKey = "flags:{$slug}";
        return Cache::remember($cacheKey, config('flags.ttl'), function () use ($slug, $fallback) {
            try {
                $url = rtrim(config('flags.base_url'), '/')."/api/installations/{$slug}/flags";
                $res = Http::get($url);

                if (!$res->ok()) return $fallback;

                $data = $res->json() ?: [];
                $features = $data['features'] ?? [];

                // Se addons=false, spegni tutto il resto
                if (isset($features['addons']) && $features['addons'] === false) {
                    $features['email_templates'] = false;
                    $features['discord_integration'] = false;
                    $features['tutorials'] = false;
                }

                // unione con fallback per valori mancanti
                return array_merge($fallback, $features);
            } catch (\Throwable $e) {
                report($e);
                return $fallback;
            }
        });
    }

    public static function enabled(string $key): bool
    {
        $flags = self::get();
        return (bool)($flags[$key] ?? false);
    }

    public static function clearCache(): void
    {
        $slug = config('flags.slug');
        if ($slug) Cache::forget("flags:{$slug}");
    }
}