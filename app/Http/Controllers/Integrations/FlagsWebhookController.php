<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class FlagsWebhookController extends Controller
{
    public function refresh(Request $r)
    {
        $raw = $r->getContent() ?? '';
        $sig = $r->header('x-signature') ?? '';
        $secret = (string) env('FLAGS_SIGNING_SECRET', '');

        // verifica HMAC SHA256 hex
        $good = hash_equals(
            hash_hmac('sha256', $raw, $secret),
            strtolower($sig)
        );
        if (!$good) return response()->json(['ok'=>false], 401);

        $slug = strtolower((string) ($r->input('slug') ?? ''));
        if (!$slug) return response()->json(['ok'=>false, 'err'=>'no slug'], 400);

        // invalida cache locale
        Cache::forget("features.remote.{$slug}");
        // se vuoi, ricarica subito: app(FlagsClient::class)->get();

        return response()->json(['ok'=>true]);
    }
}
