<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class DiscordClient
{
    protected string $token;

    public function __construct(?string $token = null)
    {
        $this->token = $token ?: config('services.discord.bot_token');
    }

    protected function http()
    {
        return Http::withToken($this->token, 'Bot')
            ->baseUrl('https://discord.com/api/v10')
            ->acceptJson()
            ->timeout(15);
    }

    public function channelMessages(string $channelId, array $params = []): array
    {
        $resp = $this->http()->get("/channels/{$channelId}/messages", $params);
        if (!$resp->successful()) {
            throw new \RuntimeException('Discord API error: '.$resp->status().' '.$resp->body());
        }
        return $resp->json();
    }

    public function channelInfo(string $channelId): array
    {
        $resp = $this->http()->get("/channels/{$channelId}");
        if (!$resp->successful()) {
            throw new \RuntimeException('Discord API error: '.$resp->status().' '.$resp->body());
        }
        return $resp->json();
    }
}