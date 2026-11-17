<?php

return [
    // true/false: se la Demo Mode è attiva
    'enabled' => (bool) env('DEMO_MODE', false),

    // opzionale: mostra il banner giallo nell’admin
    'show_banner' => (bool) env('DEMO_SHOW_BANNER', true),
];