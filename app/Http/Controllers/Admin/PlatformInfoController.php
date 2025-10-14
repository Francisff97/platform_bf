<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class PlatformInfoController extends Controller
{
    public function index(Request $request)
    {
        // === Context comune a tutti i log di questa richiesta ===
        $rid = (string) Str::uuid();             // request id univoco
        Log::withContext([
            'rid'      => $rid,
            'route'    => $request->path(),
            'user_id'  => optional($request->user())->id,
            'ip'       => $request->ip(),
            'ua'       => substr($request->userAgent() ?? '', 0, 180),
        ]);

        Log::info('platform-info.enter');

        // Helper per loggare ogni step e non far saltare tutta la pagina
        $safe = function (string $label, \Closure $fn) {
            try {
                Log::debug("platform-info.step.start", ['step' => $label]);
                $res = $fn();
                Log::debug("platform-info.step.ok",    ['step' => $label]);
                return $res;
            } catch (\Throwable $e) {
                Log::error("platform-info.step.fail", [
                    'step'   => $label,
                    'type'   => get_class($e),
                    'msg'    => $e->getMessage(),
                    'file'   => $e->getFile(),
                    'line'   => $e->getLine(),
                ]);
                // Non bloccare tutto: torna info base dell’errore in payload
                return ['error' => true, 'label' => $label, 'message' => $e->getMessage()];
            }
        };

        try {
            $data = [];

            // ---- Sezioni con try/catch individuale ----
            $data['app'] = $safe('app_info', function () {
                return [
                    'env'      => config('app.env'),
                    'debug'    => (bool) config('app.debug'),
                    'url'      => config('app.url'),
                    'timezone' => config('app.timezone'),
                    'version'  => \Illuminate\Foundation\Application::VERSION,
                    'php'      => PHP_VERSION,
                ];
            });

            $data['db'] = $safe('db_info', function () {
                // ping + versione
                $version = DB::selectOne('select version() as v');
                // tabella orders esiste?
                $ordersExists = DB::selectOne("select count(*) as cnt
                  from information_schema.tables
                  where table_schema = database() and table_name = 'orders'");
                return [
                    'version'       => $version->v ?? null,
                    'orders_exists' => (int) ($ordersExists->cnt ?? 0) === 1,
                ];
            });

            $data['storage'] = $safe('storage_info', function () {
                $pubPath = Storage::disk('public')->path('/');
                return [
                    'public_disk_path' => $pubPath,
                    'public_writable'  => @is_writable($pubPath),
                    'logs_writable'    => @is_writable(storage_path('logs')),
                ];
            });

            $data['paypal'] = $safe('paypal_keys', function () {
                return [
                    'mode'        => config('services.paypal.mode'),
                    'has_client'  => (bool) config('services.paypal.client_id'),
                    'has_secret'  => (bool) config('services.paypal.secret'),
                ];
            });

            $data['cache'] = $safe('cache_queue', function () {
                return [
                    'cache_driver' => config('cache.default'),
                    'queue_conn'   => config('queue.default'),
                ];
            });

            // Qualsiasi altra sezione che di solito usi in quella pagina:
            // $data['something'] = $safe('qualcosa', fn() => ...);

            Log::info('platform-info.exit', ['rid' => $rid]);

            // Se la tua view è blade:
            // return view('admin.platform-info', compact('data', 'rid'));

            // Se rispondi in JSON (utile per debug dal browser devtools):
            return response()->json(['rid' => $rid, 'data' => $data]);
        } catch (\Throwable $e) {
            Log::error('platform-info.exception', [
                'msg'  => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            // Torna un JSON con il rid per incrociare i log
            return response()->json([
                'error' => 'platform_info_failed',
                'rid'   => $rid,
            ], 500);
        }
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