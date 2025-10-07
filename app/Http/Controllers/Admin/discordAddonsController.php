<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DiscordMessage;
use App\Models\SiteSetting;
use Illuminate\Http\Request;

class DiscordAddonsController extends Controller
{
    public function edit(Request $request)
    {
        $s = SiteSetting::query()->first();

        $annChannel = $s->discord_announcements_channel_id ?? null;
        $fbkChannel = $s->discord_feedback_channel_id ?? null;

        $annCount = $annChannel ? DiscordMessage::where('channel_id', $annChannel)->count() : 0;
        $fbkCount = $fbkChannel ? DiscordMessage::where('channel_id', $fbkChannel)->count() : 0;

        return view('admin.addons.discord', compact('s','annCount','fbkCount'));
    }

    public function save(Request $request)
    {
        $data = $request->validate([
            'discord_server_id'                => ['nullable','string','max:30'],
            'discord_announcements_channel_id' => ['nullable','string','max:30'],
            'discord_feedback_channel_id'      => ['nullable','string','max:30'],
        ]);

        $s = SiteSetting::first() ?? new SiteSetting();
        $s->discord_server_id                 = $data['discord_server_id'] ?? '';
        $s->discord_announcements_channel_id  = $data['discord_announcements_channel_id'] ?? '';
        $s->discord_feedback_channel_id       = $data['discord_feedback_channel_id'] ?? '';
        $s->save();

        return back()->with('success', 'Discord settings salvati.');
    }

    public function sync(Request $request)
    {
        // placeholder: qui puoi richiamare una job/command per far rifare bootstrap al bot
        return back()->with('success', 'Sync avviata.');
    }
}
