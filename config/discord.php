<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Discord Webhook Secret
    |--------------------------------------------------------------------------
    |
    | Chiave usata per verificare la firma HMAC dei payload provenienti
    | dal bot di Discord. Deve essere identica a quella definita nel
    | file .env del bot remoto.
    |
    | ATTENZIONE: dopo averla modificata, ricordati di rigenerare la cache
    | con `php artisan config:cache`.
    |
    */

    'webhook_secret' => env('APP_WEBHOOK_SECRET', env('DISCORD_WEBHOOK_SECRET', null)),

];
