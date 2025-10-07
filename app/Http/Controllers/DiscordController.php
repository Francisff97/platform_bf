<?php

namespace App\Http\Controllers;

use App\Models\DiscordMessage;
use App\Models\SiteSetting; // deve avere i campi come colonne
use Illuminate\Http\Request;

class DiscordController extends Controller
{
    /**
     * GET/POST /api/discord/config
     * Legge i 3 ID dalle COLONNE della tabella site_settings:
     *  - discord_server_id
     *  - discord_announcements_channel_id
     *  - discord_feedback_channel_id
     * Fallback a .env se non presenti.
     */
    public function config(Request $req)
    {
        // prendi la prima riga delle impostazioni (adatta se hai multi-tenant)
        $s = SiteSetting::query()->first();

        $guildId = (string)($s->discord_server_id ?? env('DISCORD_GUILD_ID', ''));
        $chAnn   = (string)($s->discord_announcements_channel_id ?? env('DISCORD_CHANNEL_ANNOUNCEMENTS', ''));
        $chFb    = (string)($s->discord_feedback_channel_id      ?? env('DISCORD_CHANNEL_FEEDBACK', ''));

        // abilita solo se ho tutti e tre gli ID
        $enabled = $guildId !== '' && $chAnn !== '' && $chFb !== '';

        return response()->json([
            'ok'      => true,
            'enabled' => $enabled,
            'config'  => [
                'guild_id' => $guildId,
                'channels' => [
                    'announcements' => $chAnn,
                    'feedback'      => $chFb,
                ],
            ],
        ]);
    }

    /**
     * POST /api/discord/incoming
     * (resta uguale alla versione envelope {event,data} con HMAC)
     */
    public function incoming(Request $req)
    {
        $secret = config('discord.webhook_secret');
        $raw    = $req->getContent();
        $sig    = $req->header('x-signature', '');
        $exp    = hash_hmac('sha256', $raw, (string) $secret);

        if (!$secret || !$sig || !hash_equals($exp, $sig)) {
            return response()->json(['ok' => false, 'error' => 'invalid signature'], 401);
        }

        $json  = $req->json()->all() ?: [];
        $event = $json['event'] ?? null;
        $data  = $json['data']  ?? null;

        // compat vecchio formato "piatto"
        if (!$event && !$data) {
            $flat = $json;
            if (isset($flat['message_id']) || isset($flat['guild_id']) || isset($flat['channel_id'])) {
                $event = 'discord.message';
                $data  = $flat;
            }
        }
        if (!$event) {
            return response()->json(['ok' => false, 'error' => 'invalid payload'], 400);
        }

        // --- Aggiunta 1: pulizia globale quando arriva la nuova guild ---
        if ($event === 'bot.hello') {
            $guildId = (string)($data['guild_id'] ?? '');
            if ($guildId !== '') {
                // elimina TUTTO ciò che non appartiene alla nuova guild
                DiscordMessage::where('guild_id', '!=', $guildId)->delete();
            }
            return response()->json(['ok' => true]);
        }

        if ($event === 'discord.message') {
            $guildId = (string)($data['guild_id'] ?? '');
            $phase   = (string)($data['phase'] ?? '');

            // --- Aggiunta 2: pulizia per-guild all'inizio del bootstrap ---
            if ($phase === 'bootstrap' && $guildId !== '') {
                DiscordMessage::where('guild_id', $guildId)->delete();
            }

            $createdAt = isset($data['created_at'])
                ? now()->createFromTimestampMs((int)$data['created_at'])
                : now();

            DiscordMessage::updateOrCreate(
                ['message_id' => (string)($data['message_id'] ?? '')],
                [
                    'guild_id'           => $guildId,
                    'channel_id'         => (string)($data['channel_id'] ?? ''),
                    'channel_name'       => (string)($data['channel_name'] ?? ''),
                    'author_id'          => (string)($data['author_id'] ?? ''),
                    'author_name'        => (string)($data['author_name'] ?? ''),
                    'content'            => (string)($data['content'] ?? ''),
                    'attachments'        => $data['attachments'] ?? [],
                    'kind'               => $data['kind'] ?? null, // opzionale
                    'message_created_at' => $createdAt,
                ]
            );

            return response()->json(['ok' => true]);
        }

        return response()->json(['ok' => false, 'error' => 'unsupported event'], 400);
    }
}
