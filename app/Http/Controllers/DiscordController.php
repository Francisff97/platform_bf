<?php
namespace App\Http\Controllers;

use App\Models\DiscordMessage;
use App\Support\FeatureFlags;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class DiscordController extends Controller
{
    // Endpoint letto dal bot: ritorna config SOLO se i flag sono attivi
    public function config(Request $req)
    {
        $ff = FeatureFlags::all();
        $enabled = !empty($ff['addons']) && !empty($ff['discord_integration']);

        // recupera i 3 input salvati dall’admin (usa come preferisci: settings, .env, ecc.)
        // qui per semplicità prendo da .env, ma se hai già la pagina “Discord add-ons”
        // sostituisci con i tuoi getters.
        $cfg = [
            'guild_id'  => env('DISCORD_GUILD_ID', ''),
            'channels'  => [
                'announcements' => env('DISCORD_CHANNEL_ANNOUNCEMENTS', ''),
                'feedback'      => env('DISCORD_CHANNEL_FEEDBACK', ''),
            ],
            'features' => [
                'addons'               => (bool)($ff['addons'] ?? false),
                'discord_integration'  => (bool)($ff['discord_integration'] ?? false),
            ],
        ];

        return response()->json([
            'ok'      => true,
            'enabled' => $enabled,
            'config'  => $cfg,
        ]);
    }

    // Endpoint chiamato dal bot con i messaggi
    public function incoming(Request $req)
    {
        // verifica HMAC sha256 del raw body
        $secret = env('DISCORD_WEBHOOK_SECRET', '');
        $raw    = $req->getContent();
        $sig    = $req->header('x-signature', '');

        if (!$secret || !$sig || !hash_equals(hash_hmac('sha256', $raw, $secret), $sig)) {
            return response()->json(['ok'=>false,'error'=>'invalid signature'], 401);
        }

        $data = $req->json()->all();

        // shape prevista dal bot
        // {
        //   type: 'discord.message',
        //   event: 'create'|'bootstrap',
        //   guild_id, channel_id, channel_name,
        //   message_id, author_id, author_name, content, attachments:[], created_at
        //   kind: 'announcement'|'feedback'  <-- opzionale, lo infere dal channel id
        // }
        $channelId = (string)($data['channel_id'] ?? '');
        $kind = $data['kind'] ?? null;

        // se non passato “kind” prova a infere dai canali ENV (o da settings tuoi)
        if (!$kind) {
            $annId = env('DISCORD_CHANNEL_ANNOUNCEMENTS', '');
            $fbId  = env('DISCORD_CHANNEL_FEEDBACK', '');
            $kind  = $channelId === $annId ? 'announcement' :
                     ($channelId === $fbId  ? 'feedback'     : 'announcement');
        }

        // upsert “idempotente” su message_id
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
                'kind'               => $kind,
                'message_created_at' => isset($data['created_at']) ? now()->createFromTimestampMs((int)$data['created_at']) : now(),
            ]
        );

        return response()->json(['ok'=>true]);
    }
}
