<?php

namespace App\Http\Controllers;

use App\Models\DiscordMessage;
use App\Models\SiteSetting; // <-- usa il tuo model; se differisce vedi helper sotto
use Illuminate\Http\Request;

class DiscordController extends Controller
{
    /**
     * GET/POST /api/discord/config
     * Legge SOLO i 3 ID da SiteSettings (fallback .env) e li espone
     * nel formato atteso dal bot.
     *
     * SiteSettings (chiavi esatte):
     *  - discord_server_id
     *  - discord_announcements_channel_id
     *  - discord_feedback_channel_id
     */
    public function config(Request $req)
    {
        // prendi in un colpo solo (adatta se il tuo model/columns differiscono)
        $map = $this->getSettings([
            'discord_server_id',
            'discord_announcements_channel_id',
            'discord_feedback_channel_id',
        ]);

        $guildId = (string)($map['discord_server_id']
            ?? env('DISCORD_GUILD_ID', ''));

        $chAnn  = (string)($map['discord_announcements_channel_id']
            ?? env('DISCORD_CHANNEL_ANNOUNCEMENTS', ''));

        $chFb   = (string)($map['discord_feedback_channel_id']
            ?? env('DISCORD_CHANNEL_FEEDBACK', ''));

        // enabled solo se ho tutti e tre
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
     * Accetta l'envelope { event, data } firmato HMAC-SHA256 (header: x-signature)
     * Segreto: APP_WEBHOOK_SECRET (fallback DISCORD_WEBHOOK_SECRET).
     */
    public function incoming(Request $req)
    {
        $secret = env('APP_WEBHOOK_SECRET') ?: env('DISCORD_WEBHOOK_SECRET', '');
        $raw    = $req->getContent();
        $sig    = $req->header('x-signature', '');
        $exp    = hash_hmac('sha256', $raw, (string) $secret);

        if (!$secret || !$sig || !hash_equals($exp, $sig)) {
            return response()->json(['ok' => false, 'error' => 'invalid signature'], 401);
        }

        $json  = $req->json()->all() ?: [];
        $event = $json['event'] ?? null;
        $data  = $json['data']  ?? null;

        // compat: se arriva “piatto”, massaggia in {event,data}
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

        if ($event === 'bot.hello') {
            // opzionale: log/metrics
            return response()->json(['ok' => true]);
        }

        if ($event === 'discord.message') {
            $channelId = (string)($data['channel_id'] ?? '');
            $kind      = $data['kind'] ?? null;

            // se non passato “kind” non ci complichiamo: non inferiamo; opzionale
            // (se vuoi inferirlo, puoi ripescare i due channel id da SiteSettings come sopra)

            $createdAt = isset($data['created_at'])
                ? now()->createFromTimestampMs((int)$data['created_at'])
                : now();

            DiscordMessage::updateOrCreate(
                ['message_id' => (string)($data['message_id'] ?? '')],
                [
                    'guild_id'           => (string)($data['guild_id'] ?? ''),
                    'channel_id'         => $channelId,
                    'channel_name'       => (string)($data['channel_name'] ?? ''),
                    'author_id'          => (string)($data['author_id'] ?? ''),
                    'author_name'        => (string)($data['author_name'] ?? ''),
                    'content'            => (string)($data['content'] ?? ''),
                    'attachments'        => $data['attachments'] ?? [],
                    'kind'               => $kind, // può essere null
                    'message_created_at' => $createdAt,
                ]
            );

            return response()->json(['ok' => true]);
        }

        return response()->json(['ok' => false, 'error' => 'unsupported event'], 400);
    }

    // -------------------- helpers --------------------

    /**
     * Recupera più chiavi da SiteSettings e ritorna ['key' => 'value'].
     * Adatta questo metodo alla tua struttura:
     *  - Se hai colonne diverse (es. name/value), cambia le where/pluck.
     *  - Se hai un accessor globale (es. settings('key')), sostituisci.
     */
    private function getSettings(array $keys): array
    {
        // esempio: SiteSetting ha colonne: key, value
        $rows = SiteSetting::query()
            ->whereIn('key', $keys)
            ->pluck('value', 'key');

        return $rows->toArray();
    }
}
