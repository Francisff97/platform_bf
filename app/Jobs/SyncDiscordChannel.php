<?php

namespace App\Jobs;

use App\Models\DiscordPost;
use App\Services\DiscordClient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;

class SyncDiscordChannel implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public string $channelId;
    public string $channelType; // 'announcements' | 'feedback'
    public int $limit;

    public function __construct(string $channelId, string $channelType, int $limit = 50)
    {
        $this->channelId = $channelId;
        $this->channelType = $channelType;
        $this->limit = $limit;
    }

    public function handle(): void
    {
        $client = new DiscordClient();
        $messages = $client->channelMessages($this->channelId, ['limit' => $this->limit]);

        foreach ($messages as $m) {
            $author = Arr::get($m, 'author', []);
            $attachments = Arr::get($m, 'attachments', []);

            DiscordPost::updateOrCreate(
                ['discord_message_id' => $m['id']],
                [
                    'channel_id'    => $this->channelId,
                    'channel_type'  => $this->channelType,
                    'author_name'   => Arr::get($author,'username'),
                    'author_avatar' => Arr::get($author,'avatar') ? "https://cdn.discordapp.com/avatars/{$author['id']}/{$author['avatar']}.png" : null,
                    'content'       => Arr::get($m,'content'),
                    'attachments'   => $attachments,
                    'posted_at'     => \Carbon\Carbon::parse($m['timestamp']),
                ]
            );
        }
    }
}