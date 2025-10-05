<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class PlatformInfoController extends Controller
{
    public function index(Request $request)
    {
        // 1) URL dal config o direttamente da .env
        $url = config('app.platform_info_url', env('PLATFORM_INFO_URL'));
        abort_if(empty($url), 500, 'PLATFORM_INFO_URL non configurato');

        // 2) TTL cache
        $ttl = (int) env('PLATFORM_INFO_TTL', 3600);

        // 3) Chiave cache
        $cacheKey = 'platform-info:' . md5($url);

        // 4) Fetch con cache
        $payload = Cache::remember($cacheKey, $ttl, function () use ($url) {
            try {
                $resp = Http::timeout(10)->acceptJson()->get($url);

                if (!$resp->successful()) {
                    return [
                        'error'     => true,
                        'status'    => $resp->status(),
                        'message'   => 'HTTP request returned status code '.$resp->status().': '.$resp->body(),
                        '_meta' => [
                            'source'     => $url,
                            'fetched_at' => now()->toDateTimeString(),
                            'ttl'        => (int) env('PLATFORM_INFO_TTL', 3600),
                        ],
                    ];
                }

                $data = $resp->json();
                if (!is_array($data)) {
                    return [
                        'error'   => true,
                        'status'  => 500,
                        'message' => 'Risposta non valida: JSON non parsabile.',
                        '_meta' => [
                            'source'     => $url,
                            'fetched_at' => now()->toDateTimeString(),
                            'ttl'        => (int) env('PLATFORM_INFO_TTL', 3600),
                        ],
                    ];
                }

                // Aggancia i meta
                $data['_meta'] = [
                    'source'     => $url,
                    'fetched_at' => now()->toDateTimeString(),
                    'ttl'        => (int) env('PLATFORM_INFO_TTL', 3600),
                ];

                return $data;
            } catch (\Throwable $e) {
                return [
                    'error'   => true,
                    'status'  => 0,
                    'message' => 'Eccezione durante il fetch: '.$e->getMessage().' ',
                    '_meta' => [
                        'source'     => $url,
                        'fetched_at' => now()->toDateTimeString(),
                        'ttl'        => (int) env('PLATFORM_INFO_TTL', 3600),
                    ],
                ];
            }
        });

        // 5) Flag errore leggibile in blade
        $error = isset($payload['error']) && $payload['error'] === true;

        // 6) Passa SEMPRE $url, $info e $error
        return view('admin.platform.info', [
            'info'  => $payload,
            'url'   => $url,
            'error' => $error,
        ]);
    }

    public function refresh(Request $request)
    {
        $url = config('app.platform_info_url', env('PLATFORM_INFO_URL'));
        abort_if(empty($url), 500, 'PLATFORM_INFO_URL non configurato');

        $cacheKey = 'platform-info:' . md5($url);
        Cache::forget($cacheKey);

        return redirect()->route('admin.platform.info')
            ->with('success', 'Platform info refreshed');
    }
}