<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PlatformInfoController extends Controller
{
    public function index(Request $request)
    {
        // --- RISOLUZIONE URL ROBUSTA (supporta più chiavi) ---
        $url = config('platform.platform_info_url')
            ?: env('PLATFORM_INFO_URL')          // chiave “giusta”
            ?: env('PLATFORM_FEED_URL')          // vecchia chiave che avevi in config
            ?: env('PLATFORM_URL');              // eventuale legacy

        if (empty($url)) {
            Log::warning('platform-info: URL vuoto', [
                'route'  => 'admin.platform-info',
                'env'    => app()->environment(),
                'config' => [
                    'app.platform_info_url' => config('app.platform_info_url'),
                ],
                'env_vars_seen' => [
                    'PLATFORM_INFO_URL' => env('PLATFORM_INFO_URL'),
                    'PLATFORM_FEED_URL' => env('PLATFORM_FEED_URL'),
                    'PLATFORM_URL'      => env('PLATFORM_URL'),
                ],
            ]);
            // niente 500: mostriamo pagina “vuota” con errore leggibile
            return view('admin.platform.info', [
                'info'  => ['error' => true, 'message' => 'PLATFORM_INFO_URL non configurato'],
                'url'   => null,
                'error' => true,
            ]);
        }

        $ttl = (int) env('PLATFORM_INFO_TTL', 3600);
        $cacheKey = 'platform-info:'.md5($url);

        // flush cache manuale ?flush=1
        if ($request->boolean('flush')) {
            Cache::forget($cacheKey);
        }

        $payload = Cache::remember($cacheKey, $ttl, function () use ($url, $ttl) {
            try {
                Log::info('platform-info: fetch start', ['url' => $url]);

                $verify = filter_var(env('PLATFORM_INFO_VERIFY_SSL', true), FILTER_VALIDATE_BOOL);

                $resp = Http::timeout(10)
                    ->acceptJson()
                    ->withHeaders(['User-Agent' => 'BaseForge/PlatformInfo'])
                    ->when(!$verify, fn ($h) => $h->withOptions(['verify' => false]))
                    ->get($url);

                if (!$resp->successful()) {
                    Log::warning('platform-info: http non-200', [
                        'url' => $url, 'status' => $resp->status(), 'body' => $resp->body(),
                    ]);
                    return [
                        'error'   => true,
                        'status'  => $resp->status(),
                        'message' => 'HTTP '.$resp->status(),
                        '_meta'   => ['source'=>$url,'fetched_at'=>now()->toDateTimeString(),'ttl'=>$ttl],
                    ];
                }

                $data = $resp->json();
                if (!is_array($data)) {
                    Log::warning('platform-info: json non parsabile', ['url' => $url, 'body' => $resp->body()]);
                    return [
                        'error'   => true,
                        'status'  => 500,
                        'message' => 'JSON non parsabile',
                        '_meta'   => ['source'=>$url,'fetched_at'=>now()->toDateTimeString(),'ttl'=>$ttl],
                    ];
                }

                $data['_meta'] = ['source'=>$url,'fetched_at'=>now()->toDateTimeString(),'ttl'=>$ttl];
                Log::info('platform-info: fetch ok', ['url'=>$url]);
                return $data;

            } catch (\Throwable $e) {
                Log::error('platform-info: exception', [
                    'url' => $url, 'e' => $e->getMessage(), 'trace' => $e->getTraceAsString(),
                ]);
                return [
                    'error'   => true,
                    'status'  => 0,
                    'message' => 'Exception: '.$e->getMessage(),
                    '_meta'   => ['source'=>$url,'fetched_at'=>now()->toDateTimeString(),'ttl'=>$ttl],
                ];
            }
        });

        return view('admin.platform.info', [
            'info'  => $payload,
            'url'   => $url,
            'error' => (bool)($payload['error'] ?? false),
        ]);
    }

    public function refresh(Request $request)
    {
        $url = config('app.platform_info_url')
            ?: env('PLATFORM_INFO_URL')
            ?: env('PLATFORM_FEED_URL')
            ?: env('PLATFORM_URL');

        if (!$url) {
            Log::warning('platform-info: refresh senza URL');
            return back()->with('error','PLATFORM_INFO_URL non configurato');
        }

        Cache::forget('platform-info:'.md5($url));
        Log::info('platform-info: cache flushed', ['url' => $url]);

        return redirect()->route('admin.platform.info', ['flush'=>1])
            ->with('success','Platform info refreshed');
    }
}