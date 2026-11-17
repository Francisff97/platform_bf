<?php

namespace App\Http\Controllers;

use App\Models\DiscordMessage;
use App\Models\SiteSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class DiscordController extends Controller
{
    /**
     * GET/POST /api/discord/config
     * Ritorna gli ID (guild + canali) da SiteSetting con fallback .env
     */
    public function config(Request $req)
    {
        $s = SiteSetting::query()->first();

        $guildId = (string)($s->discord_server_id ?? env('DISCORD_GUILD_ID', ''));
        $chAnn   = (string)($s->discord_announcements_channel_id ?? env('DISCORD_CHANNEL_ANNOUNCEMENTS', ''));
        $chFb    = (string)($s->discord_feedback_channel_id      ?? env('DISCORD_CHANNEL_FEEDBACK', ''));

        $enabled = $guildId !== '' && $chAnn !== '' && $chFb !== '';

        $payload = [
            'ok'      => true,
            'enabled' => $enabled,
            'config'  => [
                'guild_id' => $guildId,
                'channels' => [
                    'announcements' => $chAnn,
                    'feedback'      => $chFb,
                ],
            ],
        ];

        // Evita cache aggressive lato CDN/client
        return Response::json($payload, 200, [
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma'        => 'no-cache',
            'Expires'       => '0',
        ]);
    }

    /**
     * POST /api/discord/incoming
     * Envelope { event, data } con firma HMAC via config('discord.webhook_secret')
     *
     * Eventi gestiti:
     * - bot.hello        → rimuove SOLO messaggi di canali non più in uso (vecchia config)
     * - discord.message  → upsert per message_id (phase: bootstrap/create/update)
     */
    public function incoming(Request $req)
    {
        // --- Verifica firma HMAC ---
        $secret = (string) config('discord.webhook_secret');
        $raw    = (string) $req->getContent();
        $sig    = strtolower((string) $req->header('x-signature',''));
        $exp    = $secret ? hash_hmac('sha256', $raw, $secret) : '';

        if (!$secret || !$sig || !hash_equals($exp, $sig)) {
            return response()->json(['ok' => false, 'error' => 'invalid signature'], 401);
        }

        $json  = $req->json()->all() ?: [];
        $event = $json['event'] ?? null;
        $data  = $json['data']  ?? null;

        // Compat vecchio formato piatto
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

        // -------------------------
        // A) bot.hello
        // -------------------------
        if ($event === 'bot.hello') {
            $guildId  = (string)($data['guild_id'] ?? '');
            $channels = $data['channels'] ?? [];

            if ($guildId === '') {
                return response()->json(['ok' => false, 'error' => 'missing_guild'], 422);
            }

            // Canali ATTUALI dichiarati dal bot
            $currAnn = (string)($channels['announcements'] ?? '');
            $currFbk = (string)($channels['feedback'] ?? '');

            // 🔥 Elimina SOLO i messaggi dei canali NON più in uso per questa guild/kind
            if ($currAnn !== '') {
                DiscordMessage::where('guild_id', $guildId)
                    ->where('kind', 'announcement')
                    ->where('channel_id', '!=', $currAnn)
                    ->delete();
            }
            if ($currFbk !== '') {
                DiscordMessage::where('guild_id', $guildId)
                    ->where('kind', 'feedback')
                    ->where('channel_id', '!=', $currFbk)
                    ->delete();
            }

            // Non tocchiamo i messaggi dei canali correnti: il bot li farà bootstrap (ultimi N)
            return response()->json(['ok' => true]);
        }

        // -------------------------
        // B) discord.message
        // -------------------------
        if ($event === 'discord.message') {
            $guildId   = (string)($data['guild_id'] ?? '');
            $messageId = (string)($data['message_id'] ?? '');
            $kind      = (string)($data['kind'] ?? ''); // 'announcement'|'feedback'
            if ($guildId === '' || $messageId === '' || $kind === '') {
                return response()->json(['ok' => false, 'error' => 'missing_fields'], 422);
            }

            // ❌ NON cancelliamo più nulla qui (prima cancellavi tutto al primo bootstrap)
            // ✅ Salviamo/aggiorniamo per message_id (upsert)
            $createdAt = isset($data['created_at'])
                ? now()->createFromTimestampMs((int)$data['created_at'])
                : now();

            DiscordMessage::updateOrCreate(
                ['message_id' => $messageId],
                [
                    'guild_id'           => $guildId,
                    'channel_id'         => (string)($data['channel_id'] ?? ''),
                    'channel_name'       => (string)($data['channel_name'] ?? ''),
                    'author_id'          => (string)($data['author_id'] ?? ''),
                    'author_name'        => (string)($data['author_name'] ?? ''),
                    'author_avatar'      => (string)($data['author_avatar'] ?? ''), // se la colonna esiste
                    'content'            => (string)($data['content'] ?? ''),
                    'attachments'        => $data['attachments'] ?? [],
                    'kind'               => $kind,
                    'message_created_at' => $createdAt,
                ]
            );

            return response()->json(['ok' => true]);
        }

        return response()->json(['ok' => false, 'error' => 'unsupported event'], 400);
    }
}
