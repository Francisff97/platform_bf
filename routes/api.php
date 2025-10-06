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
    $raw    = $r->getContent();                    // corpo *grezzo* così come arriva
    $sig    = strtolower($r->header('X-Signature', '')); // normalizziamo in lowercase
    $secret = env('FLAGS_SIGNING_SECRET','');

    // DEBUG TEMPORANEO (sicuro: non stampa il secret)
    Log::warning('[flags-refresh] IN', [
        'len'        => strlen($raw),
        'starts'     => substr($raw, 0, 40),
        'sigHeader'  => substr($sig, 0, 12),
        'secretLen'  => strlen(trim($secret)),
    ]);

    $calc = hash_hmac('sha256', $raw, $secret);

    Log::warning('[flags-refresh] CALC', [
        'calc' => substr($calc, 0, 12),
    ]);

    $ok = $secret && hash_equals($calc, $sig);
    if (!$ok) {
        // faccio anche eco (per te) delle 2 firme, solo prefisso
        return response()->json([
            'ok'        => false,
            'error'     => 'bad_signature',
            'sigHeader' => substr($sig, 0, 12),
            'sigCalc'   => substr($calc, 0, 12),
            'len'       => strlen($raw),
        ], 401);
    }

    $body  = json_decode($raw, true) ?: [];
    $slugA = data_get($body, 'slug');
    $slugB = env('FLAGS_INSTALLATION_SLUG');
    $slugC = env('FLAGS_SLUG');

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
        'values'      => $invalidated,
    ];
});

