<?php

namespace App\Http\Controllers;

use App\Models\DiscordPost;
use App\Models\SiteSetting;

class DiscordFeedController extends Controller
{
    public function news()
    {
        $s = SiteSetting::first();
        abort_unless($s?->discord_news_enabled, 404);

        $posts = DiscordPost::where('channel_type','announcements')
            ->orderByDesc('posted_at')->paginate(20);

        return view('public.discord.news', compact('posts'));
    }

    public function feedback()
    {
        $s = SiteSetting::first();
        abort_unless($s?->discord_feedback_enabled, 404);

        $posts = DiscordPost::where('channel_type','feedback')
            ->orderByDesc('posted_at')->paginate(20);

        return view('public.discord.feedback', compact('posts'));
    }
}