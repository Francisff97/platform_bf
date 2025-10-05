<?php
use App\Http\Controllers\DiscordController;
use App\Http\Controllers\DiscordWebhookController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\FlagsWebhookController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Support\FeatureFlags;

Route::get('/discord/config', [DiscordController::class, 'config']);
Route::post('/discord/incoming', [DiscordWebhookController::class, 'incoming']);
Route::post('/flags/refresh', function (Request $r) {
    $raw    = $r->getContent();
    $sig    = $r->header('X-Signature', '');
    $secret = env('FLAGS_SIGNING_SECRET','');

    $ok = $secret && hash_equals(hash_hmac('sha256', $raw, $secret), $sig);
    if (!$ok) abort(401, 'Bad signature');

    $body = json_decode($raw, true) ?: [];
    $slugA = data_get($body, 'slug');
    $slugB = env('FLAGS_INSTALLATION_SLUG');
    $slugC = env('FLAGS_SLUG');

    $invalidated = [];

    foreach (array_filter([$slugA, $slugB, $slugC]) as $s) {
        $key = "features.remote.$s";
        Cache::forget($key);
        // warm immediato
        $new = FeatureFlags::warm($s);
        $invalidated[$s] = $new;
    }

    return [
        'ok' => true,
        'invalidated' => array_keys($invalidated),
        'values' => $invalidated, // utile per debug, puoi rimuovere in prod
    ];
});