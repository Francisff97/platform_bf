<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class FlagsClient
{
    protected string $base;
    protected string $secret;
    protected string $slug;
    protected int $ttl;

    public function __construct()
    {
        // base URL del server flags (es. https://flags.tuodominio.com)
        $this->base   = rtrim(config('flags.base_url', env('FLAGS_BASE_URL', '')), '/');

        // secret HMAC condiviso con il server flags
        $this->secret = (string) env('FLAGS_SIGNING_SECRET', '');

        // slug dell’installazione (fallback a config app.slug o "demo")
        $this->slug   = (string) (config('app.slug',env('FLAGS_INSTALLATION_SLUG', env('FLAGS_SLUG', 'demo'))));

        // TTL cache in secondi
        $this->ttl    = (int) (config('flags.ttl', env('FLAGS_TTL', 60)));
    }

    /** Chiave cache locale */
    protected function cacheKey(): string
    {
        return "features.remote.{$this->slug}";
    }

    /** Firma HMAC SHA256 del body raw */
    protected function sign(string $raw): string
    {
        return hash_hmac('sha256', $raw, $this->secret);
    }

    /** Filtra solo le chiavi note delle feature */
    protected function onlyFeatureKeys(array $arr): array
    {
        $keys = ['addons','email_templates','discord_integration','tutorials','announcements'];
        return array_intersect_key($arr, array_flip($keys));
    }

    /** Helper per comporre path /api/installations/{slug}/... */
    protected function url(string $suffix): string
    {
        return "{$this->base}/api/installations/{$this->slug}{$suffix}";
    }

    /* =========================================================
     * FLAGS
     * =======================================================*/

    /** Legge i flags (con cache) */
    public function get(): array
    {
        $cacheKey = $this->cacheKey();

        return Cache::remember($cacheKey, $this->ttl, function () {
            // Se non ho base/secret -> torno dai defaults locali
            if (empty($this->base) || empty($this->secret)) {
                return ['features' => $this->onlyFeatureKeys((array) config('features'))];
            }

            try {
                $res = Http::acceptJson()
                    ->timeout(6);
                    if(app()->environment('local')){
                        $client = $client->withoutVerifying();
                    }
                    $res = $client->get($this->url('/flags'));

                if (!$res->ok()) {
                    return ['features' => $this->onlyFeatureKeys((array) config('features'))];
                }

                $json = $res->json();
                if (!is_array($json) || empty($json['features'])) {
                    return ['features' => $this->onlyFeatureKeys((array) config('features'))];
                }

                $json['features'] = $this->onlyFeatureKeys($json['features']);
                return $json;
            } catch (\Throwable $e) {
                return ['features' => $this->onlyFeatureKeys((array) config('features'))];
            }
        });
    }

    /** Scrive i flags sul server flags e invalida cache */
    public function set(array $features, ?string $actor = null): bool
    {
        if (empty($this->base) || empty($this->secret)) return false;

        $payload = json_encode(['features' => $this->onlyFeatureKeys($features)], JSON_UNESCAPED_SLASHES);
        $sig     = $this->sign($payload);

        try {
            $res = Http::withHeaders([
                    'X-Signature' => $sig,
                    'X-Actor'     => $actor ?? (auth()->user()->email ?? 'system'),
                    'Content-Type'=> 'application/json',
                    'Accept'      => 'application/json',
                ])
                ->timeout(8)
                ->withBody($payload, 'application/json')
                ->put($this->url('/flags'));

            Cache::forget($this->cacheKey());
            return $res->ok();
        } catch (\Throwable $e) {
            Cache::forget($this->cacheKey());
            return false;
        }
    }

    /* =========================================================
     * DISCORD CONFIG (guild + channels) sul server Flags
     *  - GET  /api/installations/{slug}/discord
     *  - PUT  /api/installations/{slug}/discord   (sovrascrive)
     *  - PATCH/api/installations/{slug}/discord   (merge parziale)
     * =======================================================*/

    /** Ritorna: ['guild_id' => '...', 'channels' => ['123','456']] */
    public function getDiscord(): array
    {
        if (empty($this->base)) return ['guild_id'=>'', 'channels'=>[]];

        try {
            $res = Http::acceptJson()
                ->timeout(6)
                ->get($this->url('/discord'));

            if (!$res->ok()) return ['guild_id'=>'', 'channels'=>[]];

            $j = $res->json();
            $data = $j['data'] ?? [];
            return [
                'guild_id' => (string) ($data['guild_id'] ?? ''),
                'channels' => array_values(array_map('strval', (array) ($data['channels'] ?? []))),
            ];
        } catch (\Throwable $e) {
            return ['guild_id'=>'', 'channels'=>[]];
        }
    }

    /** Sovrascrive tutta la config Discord */
    public function putDiscord(array $data, ?string $actor = null): bool
    {
        if (empty($this->base) || empty($this->secret)) return false;

        $payload = json_encode([
            'guild_id' => (string) ($data['guild_id'] ?? ''),
            'channels' => array_values(array_map('strval', (array) ($data['channels'] ?? []))),
        ], JSON_UNESCAPED_SLASHES);

        try {
            $res = Http::withHeaders([
                    'X-Signature' => $this->sign($payload),
                    'X-Actor'     => $actor ?? (auth()->user()->email ?? 'system'),
                    'Content-Type'=> 'application/json',
                    'Accept'      => 'application/json',
                ])
                ->timeout(8)
                ->withBody($payload, 'application/json')
                ->put($this->url('/discord'));

            return $res->ok();
        } catch (\Throwable $e) {
            return false;
        }
    }

    /** Patch parziale (solo i campi presenti) */
    public function patchDiscord(array $patch, ?string $actor = null): bool
    {
        if (empty($this->base) || empty($this->secret)) return false;

        $payload = json_encode($patch, JSON_UNESCAPED_SLASHES);

        try {
            $res = Http::withHeaders([
                    'X-Signature' => $this->sign($payload),
                    'X-Actor'     => $actor ?? (auth()->user()->email ?? 'system'),
                    'Content-Type'=> 'application/json',
                    'Accept'      => 'application/json',
                ])
                ->timeout(8)
                ->withBody($payload, 'application/json')
                ->patch($this->url('/discord'));

            return $res->ok();
        } catch (\Throwable $e) {
            return false;
        }
    }

    /** Permette di invalidare la cache flags manualmente */
    public function clearCache(): void
    {
        Cache::forget($this->cacheKey());
    }
     /** URL pubblico della piattaforma (env o config) */
     protected function platformUrl(): string
     {
         $url = (string) (env('PLATFORM_PUBLIC_URL', config('app.url', '')));
         return rtrim($url, '/');
     }
 
     /**
      * Registra/aggiorna l'installazione sul server Flags.
      * - Effettua un "touch" su /flags (upsert lato server).
      * - Prova a salvare platform_url se l'endpoint è disponibile (best-effort).
      */
     /** ✅ REGISTRA LA PLATFORM_URL NEI META SU FLAGS */
    public function register(string $platformUrl): bool
    {
        if (!$this->base || !$this->secret || !$this->slug) return false;

        $platformUrl = rtrim($platformUrl, '/');
        $payload = json_encode(['platform_url' => $platformUrl], JSON_UNESCAPED_SLASHES);

        $url = "{$this->base}/api/installations/{$this->slug}/meta";
        $res = Http::withHeaders([
                'X-Signature'  => $this->sign($payload),
                'Content-Type' => 'application/json',
                'X-Actor'      => 'laravel-app',
            ])->withBody($payload, 'application/json')
              ->put($url);

        // utile in caso di debug
        if (!$res->ok()) {
            \Log::error('flags.register failed', [
                'url'     => $url,
                'status'  => $res->status(),
                'body'    => $res->body(),
            ]);
        }

        return $res->ok();
    }
 
}