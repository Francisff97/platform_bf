<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use App\Models\DiscordMessage;
use Illuminate\Http\Request;

class DiscordAddonController extends Controller
{
    public function index()
    {
        $s = SiteSetting::first();

        $annChannel = $s->discord_announcements_channel_id ?? null;
        $fbkChannel = $s->discord_feedback_channel_id ?? null;

        $annCount = $annChannel ? DiscordMessage::where('channel_id', $annChannel)->count() : 0;
        $fbkCount = $fbkChannel ? DiscordMessage::where('channel_id', $fbkChannel)->count() : 0;

        // 👉 Fallback view: usa 'admin.addons.discord' se esiste,
        //    altrimenti 'admin.addons.discord.index'
        $view = view()->exists('admin.addons.discord')
            ? 'admin.addons.discord'
            : (view()->exists('admin.addons.discord.index') ? 'admin.addons.discord.index' : null);

        if (!$view) {
            // Ultima rete di sicurezza per evitare 500
            return response()->view('errors.minimal', [
                'message' => 'Discord Add-on view not found. Create either:
                resources/views/admin/addons/discord.blade.php
                or
                resources/views/admin/addons/discord/index.blade.php',
            ], 500);
        }

        return view($view, compact('s','annCount','fbkCount'));
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
        return back()->with('success','Sync avviata.');
    }
}
