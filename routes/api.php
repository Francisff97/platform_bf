<?php
use App\Http\Controllers\DiscordController;
use App\Http\Controllers\DiscordWebhookController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\FlagsWebhookController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Support\FeatureFlags;

Route::match(['GET','POST'], '/discord/config', [DiscordController::class, 'config']);
Route::match(['GET','POST'], '/discord/incoming', [DiscordWebhookController::class, 'incoming']);
Route::match(['GET','POST'], '/flags/refresh', function (\Illuminate\Http\Request $r) {
    // --- metodo --------------------------------------------------------------
    if ($r->isMethod('get')) {
        return response()->json(['ok' => true, 'method' => 'GET', 'hint' => 'send POST with HMAC'], 200);
    }

    // --- body robusto (HTTP/2/chunked ecc.) ----------------------------------
    $raw = $r->getContent();
    if ($raw === '' || $raw === null) {
        $raw = @file_get_contents('php://input') ?: '';
    }

    // --- firma header normalizzata -------------------------------------------
    $sig = strtolower(trim($r->header('X-Signature', '')));

    // --- secret: config -> fallback config -> env -> getenv -------------------
    $secret =
        trim((string) config('flags.signing_secret', '')) ?:
        trim((string) config('flags.signing_secret_fallback', '')) ?:
        trim((string) env('FLAGS_SIGNING_SECRET', '')) ?:
        trim((string) (getenv('FLAGS_SIGNING_SECRET') ?: ''));

    // --- log diagnostico essenziale ------------------------------------------
    Log::warning('[flags-refresh] IN', [
        'len'        => strlen($raw),
        'sigHeader'  => substr($sig, 0, 12),
        'secretLen'  => strlen($secret),
        'ctLen'      => $r->header('content-length'),
    ]);

    // --- calcolo HMAC e verifica ---------------------------------------------
    $calc = hash_hmac('sha256', $raw, $secret);
    Log::warning('[flags-refresh] CALC', ['calc' => substr($calc, 0, 12)]);

    $ok = $secret !== '' && $sig !== '' && hash_equals($calc, $sig);
    if (!$ok) {
        return response()->json([
            'ok'        => false,
            'error'     => 'bad_signature',
            'sigHeader' => substr($sig, 0, 12),
            'sigCalc'   => substr($calc, 0, 12),
            'len'       => strlen($raw),
        ], 401);
    }

    // --- payload --------------------------------------------------------------
    $body  = json_decode($raw, true) ?: [];
    $slugA = data_get($body, 'slug');
    $slugB = config('flags.slug');   // unico slug canonico
$slugC = null;                   // se vuoi, rimuovi del tutto questa variabile

    // --- invalidazione cache + warm ------------------------------------------
    $invalidated = [];
    foreach (array_filter([$slugA, $slugB, $slugC]) as $s) {
        $key = "features.remote.$s";
        \Cache::forget($key);
        $new = \App\Support\FeatureFlags::warm($s);
        $invalidated[$s] = $new;
    }

    return [
        'ok'          => true,
        'invalidated' => array_keys($invalidated),
        'values'      => $invalidated, // utile per debug
    ];
});


