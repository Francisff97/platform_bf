<?php

namespace App\Support;

final class VideoEmbed
{
    /**
     * Ritorna URL di embed per YouTube / Vimeo / Loom
     * oppure null se non riconosce il provider.
     */
    public static function from(?string $url): ?string
    {
        if (!$url) return null;
        $url = trim($url);

        // Normalizza: rimuovi querystring/spazi
        $clean = preg_replace('~\s+~', '', $url);

        // === YouTube ===
        // youtu.be/<id>[?params]
        if (preg_match('~^https?://youtu\.be/([A-Za-z0-9_-]{6,})~i', $clean, $m)) {
            return 'https://www.youtube.com/embed/' . $m[1];
        }
        // youtube.com/watch?v=<id>
        if (preg_match('~^https?://(?:www\.)?youtube\.com/watch\?[^#]*v=([A-Za-z0-9_-]{6,})~i', $clean, $m)) {
            return 'https://www.youtube.com/embed/' . $m[1];
        }
        // youtube shorts
        if (preg_match('~^https?://(?:www\.)?youtube\.com/shorts/([A-Za-z0-9_-]{6,})~i', $clean, $m)) {
            return 'https://www.youtube.com/embed/' . $m[1];
        }
        // youtube embed
        if (preg_match('~^https?://(?:www\.)?youtube\.com/embed/([A-Za-z0-9_-]{6,})~i', $clean, $m)) {
            return 'https://www.youtube.com/embed/' . $m[1];
        }

        // === Vimeo ===
        if (preg_match('~^https?://(?:www\.)?vimeo\.com/(\d+)~i', $clean, $m)) {
            return 'https://player.vimeo.com/video/' . $m[1];
        }
        if (preg_match('~^https?://player\.vimeo\.com/video/(\d+)~i', $clean, $m)) {
            return 'https://player.vimeo.com/video/' . $m[1];
        }

        // === Loom ===
        if (preg_match('~^https?://(?:www\.)?loom\.com/share/([A-Za-z0-9_-]{6,})~i', $clean, $m)) {
            return 'https://www.loom.com/embed/' . $m[1];
        }
        if (preg_match('~^https?://www\.loom\.com/embed/([A-Za-z0-9_-]{6,})~i', $clean, $m)) {
            return 'https://www.loom.com/embed/' . $m[1];
        }

        return null;
    }
}
