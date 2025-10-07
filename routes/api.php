<?php
use App\Http\Controllers\DiscordController;
use App\Http\Controllers\DiscordWebhookController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Config;
use App\Http\Controllers\Api\FlagsWebhookController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Support\FeatureFlags;
use Illuminate\Support\Facades\DB;
use Illuminate\Cache\RedisStore;
use Illuminate\Cache\DatabaseStore;

Route::match(['GET','POST'], '/discord/config', [DiscordController::class, 'config']);
Route::match(['GET','POST'], '/discord/incoming', [DiscordController::class, 'incoming']);
Route::post('/flags/refresh', function (Request $r) {
    // -------- 1) Signature (robusta) --------
    $raw = $r->getContent() ?? '';                     // raw body EXACT
    $sig = $r->header('X-Signature')
        ?? $r->header('X-Platform-Signature')
        ?? '';                                         // accetta entrambi gli header
    $sig = strtolower(trim(str_replace('sha256=', '', $sig))); // tollera prefisso

    // accetta chiave attuale + precedente (grace durante deploy)
    $current  = trim(Config::get('flags.signing_secret', env('FLAGS_SIGNING_SECRET', '')));
    $previous = trim(Config::get('flags.signing_secret_fallback', env('FLAGS_SIGNING_SECRET_PREV', '')));

    $calc     = $current  ? hash_hmac('sha256', $raw, $current)  : '';
    $calcPrev = $previous ? hash_hmac('sha256', $raw, $previous) : '';

    $ok = $sig && ($calc && hash_equals($calc, $sig) || $calcPrev && hash_equals($calcPrev, $sig));

    if (!$ok) {
        Log::warning('[flags-refresh] bad_signature', [
            'len'     => strlen($raw),
            'sig12'   => substr($sig, 0, 12),
            'hmac12'  => substr($calc, 0, 12),
            'prev12'  => substr($calcPrev, 0, 12),
            'hasPrev' => (bool)$previous,
        ]);
        return response()->json(['ok' => false, 'error' => 'bad_signature'], 401);
    }

    // -------- 2) Body / slugs --------
    $body  = json_decode($raw, true) ?: [];
    $slugA = data_get($body, 'slug');
    $slugB = env('FLAGS_INSTALLATION_SLUG');
    $slugC = env('FLAGS_SLUG');
    $slugs = array_values(array_unique(array_filter([$slugA, $slugB, $slugC])));

    if (empty($slugs)) {
        return response()->json(['ok' => false, 'error' => 'missing_slug'], 422);
    }

    // -------- 3) Invalidate mirato --------
    $prefix  = config('cache.prefix') ? (config('cache.prefix') . '-') : '';
    $deleted = ['database' => 0, 'redis' => 0];
    $demoKey = $prefix . 'features.remote.demo'; // legacy

    $store = Cache::getStore();

    // Database driver
    if ($store instanceof DatabaseStore) {
        $table = config('cache.stores.database.table', 'cache');
        foreach ($slugs as $s) {
            $k = $prefix . 'features.remote.' . $s;
            $deleted['database'] += DB::table($table)->where('key', $k)->delete();
        }
        $deleted['database'] += DB::table($table)->where('key', $demoKey)->delete();
        // opzionale: piazza pulita
        // $deleted['database'] += DB::table($table)->where('key','like',$prefix.'features.remote.%')->delete();
    }

    // Redis driver
    if ($store instanceof RedisStore) {
        $redis = $store->connection();
        foreach ($slugs as $s) {
            $redis->del($prefix . 'features.remote.' . $s);
        }
        $redis->del($demoKey);
        // opzionale wildcard (attenzione nei cluster!):
        // foreach ($redis->keys($prefix.'features.remote.*') as $k) { $redis->del($k); $deleted['redis']++; }
    }

    // -------- 4) Warm immediato --------
    $warmed = [];
    foreach ($slugs as $s) {
        try {
            $warmed[$s] = \App\Support\FeatureFlags::warm($s);
        } catch (\Throwable $e) {
            Log::warning('[flags-refresh] warm error', ['slug' => $s, 'err' => $e->getMessage()]);
        }
    }

    Log::info('[flags-refresh] ok', [
        'slugs' => $slugs, 'deleted' => $deleted, 'warmed_keys' => array_keys($warmed),
    ]);

    return response()->json([
        'ok'         => true,
        'invalidated'=> $slugs,
        'deleted'    => $deleted,
        'values'     => $warmed,
    ]);
})->middleware('throttle:20,1');  
