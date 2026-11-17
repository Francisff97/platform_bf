<?php
use App\Services\FlagsClient;

public function update(Request $r, FlagsClient $flags)
{
    $addons              = $r->boolean('addons');
    $emailTemplates      = $r->boolean('email_templates');
    $discordIntegration  = $r->boolean('discord_integration');
    $tutorials           = $r->boolean('tutorials');
    $announcements       = $r->boolean('announcements');

    // 1) salva in DB locale (se ne tieni traccia)
    // SiteSetting::first()->update([...])

    // 2) invia al server flags
    $ok = $flags->set([
        'addons'              => $addons,
        'email_templates'     => $emailTemplates,
        'discord_integration' => $discordIntegration,
        'tutorials'           => $tutorials,
        'announcements'       => $announcements,
    ]);

    return back()->with('success', $ok ? 'Flags aggiornati.' : 'Flags non aggiornati (server non raggiungibile).');
}