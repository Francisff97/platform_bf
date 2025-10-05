<?php
// config/features.php
return [
    'defaults' => [
        'addons'               => (bool) env('FF_ADDONS', true),
        'email_templates'      => (bool) env('FF_EMAIL_TEMPLATES', false),
        'discord_integration'  => (bool) env('FF_DISCORD_INTEGRATION', false),
        'tutorials'            => (bool) env('FF_TUTORIALS', false),
        'announcements'        => (bool) env('FF_ANNOUNCEMENTS', false),
    ],
];