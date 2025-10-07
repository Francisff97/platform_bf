<?php
use App\Http\Controllers\DiscordController;
use App\Http\Controllers\DiscordWebhookController;
use Illuminate\Support\Facades\Route;
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
Route::match(['GET','POST'], '/flags/refresh', function (\Illuminate\Http\Request $r) {
    // --- Body & Signature ---
    $raw = $r->getContent() ?: (@file_get_contents('php://input') ?: '');
    $sig = strtolower($r->header('X-Signature', ''));
    $secret = trim(config('flags.signing_secret','')) ?: trim(config('flags.signing_secret_fallback',''));

    $calc = hash_hmac('sha256', $raw, $secret);
    if (!$secret || !hash_equals($calc, $sig)) {
        Log::warning('[flags-refresh] bad sig', [
            'len'=>strlen($raw), 'sigHeader'=>substr($sig,0,12), 'sigCalc'=>substr($calc,0,12),
        ]);
        return response()->json(['ok'=>false,'error'=>'bad_signature'], 401);
    }

    $body  = json_decode($raw, true) ?: [];
    // slug esplicito dal body + eventuali fallback da .env (come prima)
    $slugA = data_get($body, 'slug');
    $slugB = env('FLAGS_INSTALLATION_SLUG');
    $slugC = env('FLAGS_SLUG');
    $slugs = array_values(array_unique(array_filter([$slugA, $slugB, $slugC])));

    // --- Purge mirato cache features.remote.* ---
    $prefix = config('cache.prefix') ? (config('cache.prefix').'-') : '';
    $deleted = ['database'=>0,'redis'=>0];
    $demoKey = $prefix.'features.remote.demo'; // legacy che vogliamo togliere comunque

    $store = Cache::getStore();

    // Database driver
    if ($store instanceof DatabaseStore) {
        $table = config('cache.stores.database.table', 'cache');
        // 1) rimuovi specifici slug (sicuro e veloce su DB)
        foreach ($slugs as $s) {
            $k = $prefix.'features.remote.'.$s;
            $deleted['database'] += DB::table($table)->where('key', $k)->delete();
        }
        // 2) rimuovi la legacy 'demo'
        $deleted['database'] += DB::table($table)->where('key', $demoKey)->delete();
        // 3) opzionale: pulizia wildcard (se vuoi proprio fare piazza pulita)
        // $deleted['database'] += DB::table($table)->where('key','like',$prefix.'features.remote.%')->delete();
    }

    // Redis driver (best-effort)
    if ($store instanceof RedisStore) {
        $redis = $store->connection();
        foreach ($slugs as $s) {
            $redis->del($prefix.'features.remote.'.$s);
        }
        $redis->del($demoKey);
        // Se vuoi wildcard:
        // foreach ($redis->keys($prefix.'features.remote.*') as $k) { $redis->del($k); $deleted['redis']++; }
    }

    // --- Warm immediato per gli slug interessati ---
    $warmed = [];
    foreach ($slugs as $s) {
        try {
            $warmed[$s] = \App\Support\FeatureFlags::warm($s);
        } catch (\Throwable $e) {
            Log::warning('[flags-refresh] warm error', ['slug'=>$s, 'err'=>$e->getMessage()]);
        }
    }

    Log::info('[flags-refresh] ok', [
        'slugs'=>$slugs, 'deleted'=>$deleted, 'warmed_keys'=>array_keys($warmed),
    ]);

    return response()->json([
        'ok'        => true,
        'invalidated' => $slugs,
        'deleted'   => $deleted,
        'values'    => $warmed,
    ]);
});


