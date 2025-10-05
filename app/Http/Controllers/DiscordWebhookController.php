<?php

namespace App\Http\Controllers;

use App\Models\DiscordMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DiscordWebhookController extends Controller
{
    public function incoming(Request $req)
    {
        // qui presumo tu abbia già middleware/firma; se no verifica qui
        $data = $req->all();

        // payload atteso dal bot
        $kind         = $data['kind'] ?? null;              // 'announcement' | 'feedback'
        $guild_id     = (string)($data['guild_id'] ?? '');
        $channel_id   = (string)($data['channel_id'] ?? '');
        $channel_name = $data['channel_name'] ?? null;
        $message_id   = (string)($data['message_id'] ?? '');
        $author_id    = $data['author_id'] ?? null;
        $author_name  = $data['author_name'] ?? null;
        $content      = $data['content'] ?? '';
        $attachments  = $data['attachments'] ?? [];
        $created_ms   = $data['created_at'] ?? null;

        if (!$kind || !$message_id) {
            return response()->json(['ok' => false, 'error' => 'invalid payload'], 400);
        }

        // upsert by message_id
        DiscordMessage::updateOrCreate(
            ['message_id' => $message_id],
            [
                'kind'         => $kind,
                'guild_id'     => $guild_id,
                'channel_id'   => $channel_id,
                'channel_name' => $channel_name,
                'author_id'    => $author_id,
                'author_name'  => $author_name,
                'content'      => $content,
                'attachments'  => $attachments,
                'posted_at'    => $created_ms ? now()->createFromTimestampMs($created_ms) : now(),
            ]
        );

        return response()->json(['ok' => true]);
    }
}