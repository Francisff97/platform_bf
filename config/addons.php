<?php
return [
    'enabled' => env('ADDONS_ENABLED', false),
    'features' => [
        'email_templates'     => env('ADDONS_EMAIL_TEMPLATES', false),
        'discord_integration' => env('ADDONS_DISCORD_INTEGRATION', false),
        'replays'             => env('ADDONS_REPLAYS', false),
    ],
];