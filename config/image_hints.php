<?php

return [
    // Model & field specific (highest priority)
    'App\\Models\\Hero.image_path'     => ['2560×1280 (min 1920×960)', '2:1',  '≤ 300 KB', 'WebP/AVIF preferred'],
    'App\\Models\\Pack.image_path'     => ['2304×840',               '~2.74:1','≤ 250 KB', 'WebP/AVIF preferred'],
    'App\\Models\\Coach.image_path'    => ['1200×675',               '16:9',   '≤ 150 KB', 'WebP/AVIF preferred'],
    'App\\Models\\Service.image_path'  => ['1200×675',               '16:9',   '≤ 150 KB', 'WebP/AVIF preferred'],
    'App\\Models\\Builder.image_path'  => ['1200×675',               '16:9',   '≤ 150 KB', 'WebP/AVIF preferred'],
    'App\\Models\\Post.image_path'     => ['1200×675',               '16:9',   '≤ 150 KB', 'WebP/AVIF preferred'],

    // Common field aliases (medium priority)
    'avatar'    => ['800×800', '1:1', '≤ 80 KB',  'WebP/AVIF preferred'],
    'logo'      => ['SVG best — or 600×300', '≈2:1', '≤ 60 KB', 'Transparent PNG/WebP OK'],
    'cover'     => ['2304×840', '~2.74:1', '≤ 250 KB', 'WebP/AVIF preferred'],
    'card'      => ['1200×675', '16:9', '≤ 150 KB', 'WebP/AVIF preferred'],
    'hero'      => ['2560×1280 (min 1920×960)', '2:1', '≤ 300 KB', 'WebP/AVIF preferred'],
    'thumbnail' => ['1200×675', '16:9', '≤ 150 KB', 'WebP/AVIF preferred'],

    // Fallback (lowest priority)
    '*'         => ['1600×900', '16:9', '≤ 200 KB', 'WebP/AVIF preferred'],
];