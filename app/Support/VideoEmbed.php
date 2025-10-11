<?php

namespace App\Support;

final class VideoEmbed
{
    /**
     * Ritorna la URL di embed (es. https://www.youtube.com/embed/ID) oppure null se non riconosciuta.
     */
    public static function from(?string $url): ?string
    {
        if (!$url) return null;
        $url = trim($url);

        // normalizza: togli spazi e decode
        $u = $url;
        // parse base
        $parts = parse_url($u);
        $host  = strtolower($parts['host'] ?? '');
        $path  = $parts['path'] ?? '';
        $query = $parts['query'] ?? '';

        // ---------
        // YouTube
        // ---------
        if (str_contains($host, 'youtu.be') || str_contains($host, 'youtube.com')) {
            // 1) youtu.be/<id>
            if (preg_match('~^/([A-Za-z0-9_-]{6,})~', $path, $m)) {
                return 'https://www.youtube.com/embed/' . $m[1];
            }

            // 2) youtube.com/watch?v=<id>
            if ($query) {
                parse_str($query, $q);
                if (!empty($q['v']) && preg_match('~^[A-Za-z0-9_-]{6,}$~', $q['v'])) {
                    return 'https://www.youtube.com/embed/' . $q['v'];
                }
            }

            // 3) youtube.com/shorts/<id>  oppure  /embed/<id>
            if (preg_match('~/(?:shorts|embed)/([A-Za-z0-9_-]{6,})~', $path, $m)) {
                return 'https://www.youtube.com/embed/' . $m[1];
            }
        }

        // ---------
        // Vimeo
        // ---------
        if (str_contains($host, 'vimeo.com')) {
            // player.vimeo.com/video/<id>  oppure  vimeo.com/<id>
            if (preg_match('~/video/([0-9]+)~', $path, $m)) {
                return 'https://player.vimeo.com/video/' . $m[1];
            }
            if (preg_match('~^/([0-9]+)~', $path, $m)) {
                return 'https://player.vimeo.com/video/' . $m[1];
            }
        }

        // ---------
        // Loom
        // ---------
        if (str_contains($host, 'loom.com')) {
            // loom.com/share/<id>  oppure  /embed/<id>
            if (preg_match('~/(?:share|embed)/([A-Za-z0-9_-]+)~', $path, $m)) {
                return 'https://www.loom.com/embed/' . $m[1];
            }
        }

        return null;
    }
}