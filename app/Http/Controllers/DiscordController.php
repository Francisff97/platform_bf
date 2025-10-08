<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use App\Models\SiteSetting;
use App\Models\DiscordPost;

class DiscordController extends Controller
{
    /**
     * /api/discord/config
     * Restituisce configurazione corrente (no-cache) per il bot.
     */
    public function config(Request $r)
    {
        $s = SiteSetting::first();

        $payload = [
            'ok' => true,
            'enabled' => true,
            'config' => [
                'guild_id' => $s?->discord_server_id ?: null,
                'channels' => [
                    'announcements' => $s?->discord_announcements_channel_id ?: null,
                    'feedback'      => $s?->discord_feedback_channel_id      ?: null,
                ],
                'features' => [
                    'news'     => (bool)($s?->discord_news_enabled     ?? true),
                    'feedback' => (bool)($s?->discord_feedback_enabled ?? true),
                ],
            ],
        ];

        // Anti-cache aggressivo
        return Response::json($payload, 200, [
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma'        => 'no-cache',
            'Expires'       => '0',
        ]);
    }

    /**
     * /api/discord/incoming
     * Eventi dal bot (firmati HMAC con APP_WEBHOOK_SECRET).
     *
     * Eventi gestiti:
     * - bot.hello        → marca generation e rimuove SOLO i post di canali non più attivi (vecchie config)
     * - discord.message  → upsert per message_id (phase: bootstrap/create/update)
     */
    public function incoming(Request $r)
    {
        // --- Verifica firma HMAC ---
        $raw = $r->getContent() ?? '';
        $sig = strtolower($r->header('X-Signature',''));
        $calc = hash_hmac('sha256', $raw, env('APP_WEBHOOK_SECRET',''));
        if (!$sig || !hash_equals($calc, $sig)) {
            Log::warning('[discord.incoming] bad_signature', ['sig12'=>substr($sig,0,12)]);
            return response()->json(['ok'=>false,'error'=>'bad_signature'], 401);
        }

        $payload = $r->json()->all();
        $event   = data_get($payload, 'event');
        $data    = data_get($payload, 'data', []);

        if ($event === 'bot.hello') {
            $guildId  = (string)($data['guild_id'] ?? '');
            $channels = $data['channels'] ?? [];
            if (!$guildId) return ['ok'=>false, 'error'=>'missing_guild'];

            // nuova "generation" (solo tag logico per i nuovi insert)
            $gen = (string) now()->timestamp;
            Cache::put("discord.gen.$guildId", $gen, 3600);

            // canali attuali
            $currAnn = (string)($channels['announcements'] ?? '');
            $currFbk = (string)($channels['feedback'] ?? '');

            // 🔥 Elimina SOLO i post di canali NON più in uso per questa guild/kind
            if ($currAnn) {
                DiscordPost::where('guild_id',$guildId)
                    ->where('kind','announcement')
                    ->where('channel_id','<>',$currAnn)
                    ->delete();
            }
            if ($currFbk) {
                DiscordPost::where('guild_id',$guildId)
                    ->where('kind','feedback')
                    ->where('channel_id','<>',$currFbk)
                    ->delete();
            }

            return ['ok'=>true, 'gen'=>$gen];
        }

        if ($event === 'discord.message') {
            $guildId   = (string)($data['guild_id'] ?? '');
            $messageId = (string)($data['message_id'] ?? '');
            $kind      = (string)($data['kind'] ?? '');
            if (!$guildId || !$messageId || !$kind) {
                return ['ok'=>false, 'error'=>'missing_fields'];
            }

            $gen = Cache::get("discord.gen.$guildId");

            // Upsert per message_id — non duplica e non perde aggiornamenti
            DiscordPost::updateOrCreate(
                ['message_id' => $messageId],
                [
                    'guild_id'      => $guildId,
                    'channel_id'    => (string)($data['channel_id'] ?? ''),
                    'kind'          => $kind,
                    'author_id'     => (string)($data['author_id'] ?? ''),
                    'author_name'   => (string)($data['author_name'] ?? ''),
                    'author_avatar' => (string)($data['author_avatar'] ?? ''),
                    'content'       => (string)($data['content'] ?? ''),
                    'attachments'   => $data['attachments'] ?? [],
                    'posted_at'     => !empty($data['created_at'])
                        ? \Carbon\Carbon::createFromTimestampMs($data['created_at'])
                        : now(),
                    'generation'    => $gen,
                ]
            );

            return ['ok'=>true];
        }

        return ['ok'=>true, 'note'=>'ignored_event'];
    }
}
