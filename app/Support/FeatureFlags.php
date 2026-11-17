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
        return rtrim((string) config('flags.base_url', ''), '/');
    }

    /** Risolve lo slug: preferisci quello passato; poi config; fallback 'demo' */
    protected static function resolveSlug(?string $slug = null): string
    {
        if (is_string($slug) && $slug !== '') {
            return strtolower(trim($slug));
        }

        // leggi solo da config
        $a = (string) config('flags.installation_slug', ''); // es. FLAGS_INSTALLATION_SLUG
        $b = (string) config('app.slug', '');                // eventuale slug app
        $c = (string) config('flags.default_slug', 'demo');  // fallback

        $out = trim($a) !== '' ? $a : (trim($b) !== '' ? $b : $c);
        return strtolower(trim($out));
    }

    /** Tenuta per compat: non più usata in rememberForever */
    protected static function ttl(): int
    {
        return (int) config('flags.ttl', 60);
    }

    protected static function forceLocal(): bool
    {
        return (bool) config('features.force_local', false);
    }

    /** true/false robusto da stringhe di config */
    protected static function toBool($v): bool
    {
        if (is_bool($v)) return $v;
        $s = strtolower(trim((string) $v));
        return in_array($s, ['1','true','yes','on','y','t'], true);
    }

    /** Override locali presi da config/features.php → ['overrides' => [...]] */
    protected static function localFromEnv(array $defaults): array
    {
        // leggiamo da config('features.overrides.*'), non da env()
        $map = [
            'addons'              => 'features.overrides.addons',
            'email_templates'     => 'features.overrides.email_templates',
            'discord_integration' => 'features.overrides.discord_integration',
            'tutorials'           => 'features.overrides.tutorials',
            'announcements'       => 'features.overrides.announcements',
        ];

        $out = [];
        foreach ($map as $key => $confKey) {
            $raw = config($confKey, null);
            if ($raw === null) continue; // nessun override configurato
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
     *  - se FEATURES_FORCE_LOCAL=true ⇒ defaults + overrides da config
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
     * route('slug') → ?installation= → DB settings → config → 'demo'
     */
    public static function currentSlug(): string
    {
        $r = request();

        $fromRoute = $r?->route('slug');                         // es: /admin/{slug}
        $fromQuery = $r?->query('installation');                 // es: ?installation=dnln
        $fromDb    = optional(\App\Models\SiteSetting::first())->flags_installation_slug;

        // solo config, niente env()
        $fromCfg   = (string) config('flags.installation_slug', '');
        $fallback  = (string) (config('app.slug', '') ?: config('flags.default_slug', 'demo'));

        $slug = $fromRoute ?? $fromQuery ?? $fromDb ?? ($fromCfg !== '' ? $fromCfg : $fallback);

        return strtolower(trim((string) $slug));
    }
}
