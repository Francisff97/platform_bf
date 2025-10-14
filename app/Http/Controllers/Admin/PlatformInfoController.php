<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PlatformInfoController extends Controller
{
    public function index(Request $request)
    {
        $rid = (string) Str::uuid();                 // trace id per correlare i log
        Log::withContext(['rid' => $rid, 'route' => 'admin.platform-info']);

        // 1) URL dal config o direttamente da .env
        $url = config('app.platform_info_url', env('PLATFORM_INFO_URL'));

        if (empty($url)) {
            Log::error('platform_info.misconfigured', [
                'reason' => 'PLATFORM_INFO_URL assente'
            ]);
            abort(500, 'PLATFORM_INFO_URL non configurato');
        }

        // 2) TTL cache
        $ttl = (int) env('PLATFORM_INFO_TTL', 3600);

        // 3) Chiave cache
        $cacheKey = 'platform-info:' . md5($url);

        // 4) Fetch con cache (loggo sia hit che miss)
        $payload = Cache::remember($cacheKey, $ttl, function () use ($url, $ttl) {
            Log::info('platform_info.cache_miss', ['url' => $url]);

            try {
                $resp = Http::retry(1, 300)          // 1 retry rapido
                    ->timeout(8)
                    ->acceptJson()
                    ->withHeaders([
                        'X-Request-Id' => Log::getContext()['rid'] ?? null,
                        'User-Agent'   => 'BaseForge/PlatformInfo',
                    ])
                    ->get($url);

                if (!$resp->successful()) {
                    $body = $this->truncate($resp->body(), 4000);
                    Log::warning('platform_info.http_failed', [
                        'status' => $resp->status(),
                        'url'    => $url,
                        'body'   => $this->truncate(str_replace(["\r", "\n"], ' ', $body), 500),
                    ]);

                    return [
                        'error'   => true,
                        'status'  => $resp->status(),
                        'message' => 'HTTP request returned status code '.$resp->status(),
                        '_meta'   => [
                            'source'     => $url,
                            'fetched_at' => now()->toDateTimeString(),
                            'ttl'        => (int) env('PLATFORM_INFO_TTL', 3600),
                        ],
                        '_raw'    => $body, // utile in view/log
                    ];
                }

                $data = $resp->json();

                if (!is_array($data)) {
                    Log::warning('platform_info.invalid_json', [
                        'status' => $resp->status(),
                        'url'    => $url,
                        'body'   => $this->truncate($resp->body(), 400), // per capire cos’è arrivato
                    ]);

                    return [
                        'error'   => true,
                        'status'  => 500,
                        'message' => 'Risposta non valida: JSON non parsabile.',
                        '_meta'   => [
                            'source'     => $url,
                            'fetched_at' => now()->toDateTimeString(),
                            'ttl'        => (int) env('PLATFORM_INFO_TTL', 3600),
                        ],
                    ];
                }

                $data['_meta'] = [
                    'source'     => $url,
                    'fetched_at' => now()->toDateTimeString(),
                    'ttl'        => (int) env('PLATFORM_INFO_TTL', 3600),
                ];

                Log::info('platform_info.ok', [
                    'url'   => $url,
                    'keys'  => array_keys($data),
                    'cache' => 'store',
                    'ttl'   => $ttl,
                ]);

                return $data;
            } catch (\Throwable $e) {
                Log::error('platform_info.exception', [
                    'url'   => $url,
                    'ex'    => get_class($e),
                    'msg'   => $e->getMessage(),
                ]);

                return [
                    'error'   => true,
                    'status'  => 0,
                    'message' => 'Eccezione durante il fetch: '.$e->getMessage(),
                    '_meta'   => [
                        'source'     => $url,
                        'fetched_at' => now()->toDateTimeString(),
                        'ttl'        => (int) env('PLATFORM_INFO_TTL', 3600),
                    ],
                ];
            }
        });

        // log cache hit (solo se non abbiamo scritto dentro al remember)
        if (Cache::has($cacheKey)) {
            Log::debug('platform_info.cache_hit', ['url' => $url]);
        }

        // 5) Flag errore per blade
        $error = isset($payload['error']) && $payload['error'] === true;

        // 6) Passa dati alla view
        return view('admin.platform.info', [
            'info'  => $payload,
            'url'   => $url,
            'error' => $error,
            'rid'   => $rid,
        ]);
    }

    public function refresh(Request $request)
    {
        $rid = (string) Str::uuid();
        Log::withContext(['rid' => $rid, 'route' => 'admin.platform-info.refresh']);

        $url = config('app.platform_info_url', env('PLATFORM_INFO_URL'));
        if (empty($url)) {
            Log::error('platform_info.misconfigured_refresh', ['reason' => 'PLATFORM_INFO_URL assente']);
            abort(500, 'PLATFORM_INFO_URL non configurato');
        }

        $cacheKey = 'platform-info:' . md5($url);
        Cache::forget($cacheKey);
        Log::notice('platform_info.cache_forced_refresh', ['url' => $url, 'cache_key' => $cacheKey]);

        return redirect()->route('admin.platform.info')
            ->with('success', 'Platform info refreshed');
    }

    private function truncate(?string $text, int $max = 4000): string
    {
        $text = (string) $text;
        return mb_strlen($text) > $max ? (mb_substr($text, 0, $max).'…') : $text;
    }
}