<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use App\Models\DiscordMessage; // <-- come il vecchio controller
use Illuminate\Http\Request;

class DiscordAddonController extends Controller
{
    /**
     * Mostra la pagina Discord Add-on (stessa UX del vecchio controller "Addons")
     * Route: GET /admin/addons/discord  -> name: admin.addons.discord
     */
    public function index()
    {
        $s = SiteSetting::first();

        $annChannel = $s->discord_announcements_channel_id ?? null;
        $fbkChannel = $s->discord_feedback_channel_id ?? null;

        // Conteggi come nel vecchio controller: per channel_id su DiscordMessage
        $annCount = $annChannel ? DiscordMessage::where('channel_id', $annChannel)->count() : 0;
        $fbkCount = $fbkChannel ? DiscordMessage::where('channel_id', $fbkChannel)->count() : 0;

        // View identica a prima (non .../index)
        return view('admin.addons.discord', compact('s','annCount','fbkCount'));
    }

    /**
     * Salva le impostazioni Discord (3 campi, come prima)
     * Route: POST /admin/addons/discord -> name: admin.addons.discord.save
     */
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

    /**
     * Placeholder sync (come prima)
     * Route: GET /admin/addons/discord/sync -> name: admin.addons.discord.sync
     */
    public function sync(Request $request)
    {
        // (nessun job; manteniamo lo stesso comportamento del vecchio controller)
        return back()->with('success', 'Sync avviata.');
    }
}
