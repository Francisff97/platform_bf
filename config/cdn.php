<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cloudflare Images / CDN config
    |--------------------------------------------------------------------------
    |
    | Questo flag controlla se il componente <x-img> deve generare gli URL
    | ottimizzati tramite "/cdn-cgi/image/...". Se è false, verranno usate
    | direttamente le immagini locali su /storage.
    |
    */

    'use_cloudflare' => env('USE_CF_IMAGE', false),

    /*
    |--------------------------------------------------------------------------
    | Impostazioni default delle immagini (solo se CF è attivo)
    |--------------------------------------------------------------------------
    |
    | Questi valori servono per le dimensioni e la qualità di default.
    | Puoi cambiarli globalmente senza toccare il componente <x-img>.
    |
    */

    'quality' => env('CDN_IMG_QUALITY', 82),
    'fit'     => env('CDN_IMG_FIT', 'cover'),

    /*
    |--------------------------------------------------------------------------
    | Breakpoints (width in px)
    |--------------------------------------------------------------------------
    | Verranno usati per generare lo srcset quando Cloudflare è attivo.
    |
    */

    'breakpoints' => [
        768, 1280, 1920,
    ],
];