<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FeatureFlags
{
    /** Defaults di progetto (se hai config/features.php puoi metterli lì) */
    protected static function defaults(): array
    {
        return config('features.defaults') ?? [
            'addons'              => true,
            'email_templates'     => false,
            'discord_integration' => false,
            'tutorials'           => false,
            'announcements'       => false,
        ];
    }

    protected static function baseUrl(): string
    {
        return rtrim(config('flags.base_url', env('FLAGS_BASE_URL', '')), '/');
    }

    /** Risolve lo slug: preferisci quello passato; poi ENV; poi config; fallback 'demo' */
    protected static function resolveSlug(?string $slug = null): string
    {
        if (is_string($slug) && $slug !== '') {
            return strtolower(trim($slug));
        }

        $a = config('flags.installations.slug');
        $b = env('flags.slug');
        $c = config('app.slug');

        $out = $a ?: ($b ?: ($c ?: 'demo'));
        return strtolower(trim((string) $out));
    }

    /** Tenuta per compat: non più usata in rememberForever */
    protected static function ttl(): int
    {
        return (int) config('flags.ttl', env('FLAGS_TTL', 60));
    }

    protected static function forceLocal(): bool
    {
        return (bool) env('FEATURES_FORCE_LOCAL', false);
    }

    /** true/false robusto da stringhe ENV */
    protected static function toBool($v): bool
    {
        if (is_bool($v)) return $v;
        $s = strtolower(trim((string) $v));
        return in_array($s, ['1','true','yes','on','y','t'], true);
    }

    /** Override locali da ENV singole */
    protected static function localFromEnv(array $defaults): array
    {
        $map = [
            'addons'              => 'FEATURES_ADDONS',
            'email_templates'     => 'FEATURES_EMAIL_TEMPLATES',
            'discord_integration' => 'FEATURES_DISCORD_INTEGRATION',
            'tutorials'           => 'FEATURES_TUTORIALS',
            'announcements'       => 'FEATURES_ANNOUNCEMENTS',
        ];

        $out = [];
        foreach ($map as $key => $envKey) {
            $raw = env($envKey, null);
            if ($raw === null) continue;
            $out[$key] = self::toBool($raw);
        }

        // riempi sempre tutte le chiavi con i defaults
        return array_merge($defaults, array_intersect_key($out, $defaults));
    }

    /* ----------------------------------------------------------------------
     |  FETCH REMOTO (per slug specifico)
     * --------------------------------------------------------------------*/
    protected static function fetchRemoteFor(string $slug, array $defaults): ?array
    {
        $base = self::baseUrl();
        if (!$base || !$slug) return null;

        try {
            $client = Http::acceptJson()->timeout(6);
            if (app()->environment('local')) {
                // utile in locale se hai problemi di certificati
                $client = $client->withoutVerifying();
            }

            $res = $client->get($base . "/api/installations/{$slug}/flags");
            if (!$res->ok()) {
                Log::notice("FeatureFlags remote HTTP {$res->status()}");
                return null;
            }

            $json = $res->json();

            // Forma 1: { "features": { ... } }
            if (is_array($json) && isset($json['features']) && is_array($json['features'])) {
                return array_merge($defaults, array_intersect_key($json['features'], $defaults));
            }

            // Forma 2 (compat): { "value": "{\"features\":{...}}" }
            if (is_array($json) && isset($json['value']) && is_string($json['value'])) {
                $decoded = json_decode($json['value'], true);
                if (is_array($decoded) && isset($decoded['features']) && is_array($decoded['features'])) {
                    return array_merge($defaults, array_intersect_key($decoded['features'], $defaults));
                }
            }

            return null;
        } catch (\Throwable $e) {
            Log::notice('FeatureFlags remote fetch error: '.$e->getMessage());
            return null;
        }
    }

    /** Retro-compat: fetch remoto per lo slug risolto */
    protected static function fetchRemote(array $defaults, ?string $slug = null): ?array
    {
        $slug = self::resolveSlug($slug);
        return self::fetchRemoteFor($slug, $defaults);
    }

    /* ----------------------------------------------------------------------
     |  Cache helpers
     * --------------------------------------------------------------------*/
    public static function cacheKey(?string $slug = null): string
    {
        $slug = self::resolveSlug($slug);
        return "features.remote.$slug";
    }

    /** Invalida la cache dei flags per uno slug (o quello corrente) */
    public static function clearCache(?string $slug = null): void
    {
        Cache::forget(self::cacheKey($slug));
    }

    /**
     * Warm esplicito: ricarica da remoto e salva forever.
     * Ritorna i valori salvati in cache.
     */
    public static function warm(?string $slug = null): array
    {
        $defaults = self::defaults();
        $locals   = self::localFromEnv($defaults);
        $slug     = self::resolveSlug($slug);
        $cacheKey = self::cacheKey($slug);

        $remote = self::fetchRemoteFor($slug, $defaults);
        $final  = $remote ?? $locals;

        Cache::forever($cacheKey, $final);
        return $final;
    }

    /**
     * PRIORITÀ:
     *  - se FEATURES_FORCE_LOCAL=true ⇒ defaults + ENV
     *  - altrimenti: REMOTO (cache forever) per LO SLUG PASSATO (o quello “corrente”)
     */
    public static function all(?string $slug = null): array
    {
        $defaults = self::defaults();
        $locals   = self::localFromEnv($defaults);

        if (self::forceLocal()) {
            return $locals;
        }

        $slug     = self::resolveSlug($slug);
        $cacheKey = self::cacheKey($slug);

        try {
            return Cache::rememberForever($cacheKey, function () use ($defaults, $locals, $slug) {
                $remote = self::fetchRemoteFor($slug, $defaults);
                return $remote ?? $locals;
            });
        } catch (\Throwable $e) {
            return $locals;
        }
    }

    /** Helper singolo flag (accetta lo slug) */
    public static function enabled(string $key, ?string $slug = null): bool
    {
        $all = self::all($slug);
        return !empty($all[$key]);
    }

    /** DEBUG: utile in tinker per capire cosa legge (accetta slug) */
    public static function debug(?string $slug = null): array
    {
        $slug = self::resolveSlug($slug);
        return [
            'base_url'   => self::baseUrl(),
            'slug'       => $slug,
            'forceLocal' => self::forceLocal(),
            'defaults'   => self::defaults(),
            'locals'     => self::localFromEnv(self::defaults()),
            'remote'     => self::fetchRemote(self::defaults(), $slug),
            'final'      => self::all($slug),
        ];
    }

    /**
     * Slug “corrente” per Blade/Controller:
     * route('slug') → ?installation= → DB settings → ENV → 'demo'
     */
    public static function currentSlug(): string
    {
        $r = request();

        $fromRoute = $r?->route('slug');                 // es: /admin/{slug}
        $fromQuery = $r?->query('installation');         // es: ?installation=dnln
        $fromDb    = optional(\App\Models\SiteSetting::first())->flags_installation_slug;
        $fromEnv   = env('FLAGS_INSTALLATION_SLUG') ?: env('FLAGS_SLUG') ?: config('app.slug');

        $slug = $fromRoute ?? $fromQuery ?? $fromDb ?? $fromEnv ?? 'demo';

        return strtolower(trim((string) $slug));
    }
}
