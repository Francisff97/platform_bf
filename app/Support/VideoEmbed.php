<?php

namespace App\Support;

final class VideoEmbed
{
    /**
     * Accetta URL YouTube/Vimeo/MP4 ecc. e ritorna URL di EMBED, oppure null.
     * Esempi input validi:
     *  - https://youtu.be/VIDEOID
     *  - https://www.youtube.com/watch?v=VIDEOID
     *  - https://www.youtube.com/shorts/VIDEOID
     *  - https://vimeo.com/12345678
     *  - https://player.vimeo.com/video/12345678
     *  - https://cdn.site.com/video.mp4
     */
    public static function from(?string $url): ?string
    {
        if (!$url) return null;
        $url = trim($url);

        // MP4 / file diretti: li lasciamo così (iframe li mostra, oppure usa <video>)
        if (preg_match('~\.(mp4|webm|ogg)(\?.*)?$~i', $url)) {
            return $url;
        }

        // YOUTUBE (watch, youtu.be, shorts)
        // normalizza in https://www.youtube.com/embed/VIDEOID
        if (preg_match('~^(https?://)?(www\.)?(youtube\.com|youtu\.be)~i', $url)) {
            // youtu.be/ID
            if (preg_match('~youtu\.be/([A-Za-z0-9_\-]{6,})~', $url, $m)) {
                return 'https://www.youtube.com/embed/' . $m[1];
            }
            // youtube.com/shorts/ID
            if (preg_match('~youtube\.com/shorts/([A-Za-z0-9_\-]{6,})~', $url, $m)) {
                return 'https://www.youtube.com/embed/' . $m[1];
            }
            // youtube.com/watch?v=ID
            if (preg_match('~v=([A-Za-z0-9_\-]{6,})~', $url, $m)) {
                return 'https://www.youtube.com/embed/' . $m[1];
            }
            // se già embed
            if (str_contains($url, '/embed/')) {
                return $url;
            }
            // fallback grezzo: prova a ricavare l’ultimo segmento come id
            $id = basename(parse_url($url, PHP_URL_PATH) ?? '');
            if ($id && strlen($id) >= 6) {
                return 'https://www.youtube.com/embed/' . $id;
            }
            return null;
        }

        // VIMEO
        // normalizza in https://player.vimeo.com/video/ID
        if (preg_match('~^(https?://)?(www\.)?(vimeo\.com|player\.vimeo\.com)~i', $url)) {
            // player.vimeo.com/video/ID -> già ok
            if (preg_match('~player\.vimeo\.com/video/(\d+)~', $url, $m)) {
                return 'https://player.vimeo.com/video/' . $m[1];
            }
            // vimeo.com/ID
            if (preg_match('~vimeo\.com/(\d+)~', $url, $m)) {
                return 'https://player.vimeo.com/video/' . $m[1];
            }
            return null;
        }

        // altri provider: restituisci l’URL (se è iframe-able e consentito dalla CSP)
        return $url;
    }
    public static function youtubeId(?string $url): ?string
    {
        if (!$url) return null;
        $url = trim($url);

        // casi: youtu.be/ID
        if (preg_match('~youtu\.be/([A-Za-z0-9_-]{6,})~i', $url, $m)) return $m[1];

        // casi: /embed/ID
        if (preg_match('~/embed/([A-Za-z0-9_-]{6,})~i', $url, $m)) return $m[1];

        // casi: watch?v=ID
        if (preg_match('~v=([A-Za-z0-9_-]{6,})~i', $url, $m)) return $m[1];

        // casi: shorts/ID
        if (preg_match('~/shorts/([A-Za-z0-9_-]{6,})~i', $url, $m)) return $m[1];

        return null;
    }
}
