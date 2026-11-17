<?php

namespace App\Http\Controllers\Integrations;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DiscordWebhookController extends Controller
{
    public function incoming(Request $request)
    {
        if (!filter_var(env('DISCORD_INCOMING_ENABLED', true), FILTER_VALIDATE_BOOL)) {
            return response()->json(['ok' => false, 'error' => 'disabled'], 403);
        }

        // 1) verifica firma
        $secret = env('APP_WEBHOOK_SECRET', '');
        if (!$secret) return response()->json(['ok'=>false,'error'=>'missing secret'], 500);

        $raw = $request->getContent();
        $given = $request->header('x-signature', '');
        $calc  = hash_hmac('sha256', $raw, $secret);

        if (!hash_equals($calc, $given)) {
            return response()->json(['ok'=>false,'error'=>'invalid signature'], 401);
        }

        // 2) flags: blocca se il feature è OFF
        if (!\App\Services\Flags::enabled('discord_integration')) {
            return response()->json(['ok'=>false,'error'=>'feature off'], 403);
        }

        // 3) payload
        $payload = $request->json()->all();
        // Esempio: salvalo in DB / coda / log
        Log::info('discord.incoming', $payload);

        // TODO: qui puoi creare un model Announcement/Feedback e salvare
        // in base a channel_name o guild_id.

        return response()->json(['ok' => true]);
    }
}