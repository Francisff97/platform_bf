<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use App\Models\DiscordPost;
use App\Jobs\SyncDiscordChannel;
use Illuminate\Http\Request;

class DiscordAddonController extends Controller
{
    public function index()
    {
        $s = SiteSetting::first();
        $annCount = $s?->discord_announcements_channel_id ? DiscordPost::where('channel_type','announcements')->count() : 0;
        $fbkCount = $s?->discord_feedback_channel_id ? DiscordPost::where('channel_type','feedback')->count() : 0;

        return view('admin.addons.discord.index', [
            's' => $s,
            'annCount' => $annCount,
            'fbkCount' => $fbkCount,
        ]);
    }

    public function save(Request $request)
    {
        $data = $request->validate([
            'discord_server_id'                 => ['nullable','string'],
            'discord_announcements_channel_id' => ['nullable','string'],
            'discord_feedback_channel_id'      => ['nullable','string'],
            'discord_news_enabled'             => ['nullable','boolean'],
            'discord_feedback_enabled'         => ['nullable','boolean'],
        ]);

        $s = \App\Models\SiteSetting::firstOrCreate([]);
        $s->fill([
            'discord_server_id'                 => $data['discord_server_id'] ?? null,
            'discord_announcements_channel_id' => $data['discord_announcements_channel_id'] ?? null,
            'discord_feedback_channel_id'      => $data['discord_feedback_channel_id'] ?? null,
            'discord_news_enabled'             => (bool)($data['discord_news_enabled'] ?? false),
            'discord_feedback_enabled'         => (bool)($data['discord_feedback_enabled'] ?? false),
        ]);
        $s->save();

        return back()->with('success','Discord settings saved.');
    }

    public function sync(Request $request)
    {
        $s = SiteSetting::first();
        if ($s?->discord_announcements_channel_id) {
            SyncDiscordChannel::dispatch($s->discord_announcements_channel_id,'announcements');
        }
        if ($s?->discord_feedback_channel_id) {
            SyncDiscordChannel::dispatch($s->discord_feedback_channel_id,'feedback');
        }
        return back()->with('success','Sync queued.');
    }
}