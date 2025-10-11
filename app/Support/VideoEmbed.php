<?php

namespace App\Support;

final class VideoEmbed
{
    public static function from(?string $url): ?string
    {
        if (!$url) return null;
        $url = trim($url);

        // YouTube (normale / youtu.be / shorts)
        if (preg_match('~^(?:https?://)?(?:www\.)?(?:youtube\.com|youtu\.be)/(?:watch\?v=|shorts/|embed/)?([A-Za-z0-9_-]{6,})~i', $url, $m)) {
            return "https://www.youtube.com/embed/{$m[1]}";
        }

        // Vimeo
        if (preg_match('~^(?:https?://)?(?:www\.)?vimeo\.com/(\d+)~i', $url, $m)) {
            return "https://player.vimeo.com/video/{$m[1]}";
        }

        // Loom
        if (preg_match('~^(?:https?://)?(?:www\.)?loom\.com/share/([A-Za-z0-9-]+)~i', $url, $m)) {
            return "https://www.loom.com/embed/{$m[1]}";
        }

        // fallback: url diretto in iframe
        return $url;
    }
}
