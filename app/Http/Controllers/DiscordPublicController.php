<?php

namespace App\Http\Controllers;

use App\Models\DiscordMessage;
use Illuminate\Http\Request;

class DiscordPublicController extends Controller
{
    public function announcements()
    {
        $items = DiscordMessage::where('kind','announcement')
            ->latest('posted_at')
            ->paginate(12);

        return view('discord.announcements', compact('items'));
    }

    public function feedback()
    {
        $items = DiscordMessage::where('kind','feedback')
            ->latest('posted_at')
            ->paginate(12);

        return view('discord.feedback', compact('items'));
    }
}
