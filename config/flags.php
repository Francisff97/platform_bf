<?php
return [
    'base_url' => env('FLAGS_BASE_URL', ''),
    'installation_slug'  => env('FLAGS_INSTALLATION_SLUG', ''),
    'slug'               => env('FLAGS_SLUG', ''),
    'ttl'      => 60,
    // tieni il nome che già usi su FLAGS
    'signing_secret' => env('FLAGS_SIGNING_SECRET', ''),

    // opzionale fallback per retrocompatibilità
    'signing_secret_fallback' => env('SIGNING_SECRET', ''),

];
