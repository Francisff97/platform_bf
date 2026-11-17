<?php

namespace App\Console\Commands;

use App\Jobs\SyncDiscordChannel;
use App\Models\SiteSetting;
use Illuminate\Console\Command;

class DiscordSyncCommand extends Command
{
    protected $signature = 'discord:sync';
    protected $description = 'Fetch latest messages from configured Discord channels';

    public function handle(): int
    {
        $s = SiteSetting::first();
        if (!$s) { $this->error('SiteSetting missing'); return self::FAILURE; }

        $ann = $s->discord_announcements_channel_id;
        $fbk = $s->discord_feedback_channel_id;

        if ($ann) {
            SyncDiscordChannel::dispatch($ann, 'announcements');
            $this->info('Queued announcements sync');
        }
        if ($fbk) {
            SyncDiscordChannel::dispatch($fbk, 'feedback');
            $this->info('Queued feedback sync');
        }
        if (!$ann && !$fbk) {
            $this->warn('No channels configured.');
        }
        return self::SUCCESS;
    }
}