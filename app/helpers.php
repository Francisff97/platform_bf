<?php
use App\Support\Currency;
if (!function_exists('embed_from_url')) {
    function embed_from_url(string $url): ?string {
        if (preg_match('~(?:youtube\.com/watch\?v=|youtu\.be/)([A-Za-z0-9_-]{6,})~', $url, $m)) {
            return 'https://www.youtube.com/embed/'.$m[1];
        }
        if (preg_match('~vimeo\.com/(\d+)~', $url, $m)) {
            return 'https://player.vimeo.com/video/'.$m[1];
        }
        return null; // usa link diretto
    }
    if (!function_exists('money_site')) {
    // formatta centesimi nella valuta del sito (senza conversione)
    function money_site(int $cents): string {
        $site = Currency::site();
        return Currency::format($cents, $site['code']);
    }
}

if (!function_exists('money_convert_and_format')) {
    // converte da $from verso la valuta del sito, poi formatta
    function money_convert_and_format(int $cents, string $from): string {
        $site = Currency::site();
        $conv = Currency::convertCents($cents, strtoupper($from), $site['code'], $site['fx']);
        return Currency::format($conv, $site['code']);
    }
}
}