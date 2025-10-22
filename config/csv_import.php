<?php

return [
    // Quanti record processare per chunk (anche per code)
    'chunk' => 1000,

    // Mappatura per entità
    'entities' => [

        'users' => [
            'model'      => App\Models\User::class,
            'unique_by'  => ['email'], // upsert key
            'fillable'   => [
                'name'      => ['label'=>'Name'],
                'email'     => ['label'=>'Email'],
                'password'  => ['label'=>'Password', 'transform'=>'hash_if_plain'],
            ],
            'rules'      => [
                'email' => ['required','email'],
            ],
            'defaults'   => [
                // opzionale: ruoli, ecc.
            ],
        ],

        'builders' => [
            'model'      => App\Models\Builder::class,
            'unique_by'  => ['slug'],
            'fillable'   => [
                'name'        => ['label'=>'Name'],
                'slug'        => ['label'=>'Slug'],
                'team'        => ['label'=>'Team'],
                'image_path'  => ['label'=>'Image Path'],
                'skills'      => ['label'=>'Skills (JSON|CSV)', 'transform'=>'json_or_csv_array'],
                'description' => ['label'=>'Description'],
            ],
            'rules'      => [
                'name' => ['required'],
                'slug' => ['required'],
            ],
            'defaults'   => [],
        ],

        'coaches' => [
            'model'      => App\Models\Coach::class,
            'unique_by'  => ['slug'],
            'fillable'   => [
                'name'        => ['label'=>'Name'],
                'slug'        => ['label'=>'Slug'],
                'team'        => ['label'=>'Team'],
                'image_path'  => ['label'=>'Image Path'],
                'skills'      => ['label'=>'Skills (JSON|CSV)', 'transform'=>'json_or_csv_array'],
                'bio'         => ['label'=>'Bio'],
            ],
            'rules'      => [
                'name' => ['required'],
                'slug' => ['required'],
            ],
            'defaults'   => [],
        ],

        'packs' => [
            'model'      => App\Models\Pack::class,
            'unique_by'  => ['slug'],
            'fillable'   => [
                'title'        => ['label'=>'Title'],
                'slug'         => ['label'=>'Slug'],
                'excerpt'      => ['label'=>'Excerpt'],
                'description'  => ['label'=>'Description'],
                'price_cents'  => ['label'=>'Price (cents)', 'transform'=>'int'],
                'currency'     => ['label'=>'Currency'],
                'image_path'   => ['label'=>'Image Path'],
                'category_id'  => ['label'=>'Category ID', 'transform'=>'int'],
                'builder_id'   => ['label'=>'Builder ID', 'transform'=>'int'],
            ],
            'rules'      => [
                'title' => ['required'],
                'slug'  => ['required'],
            ],
            'defaults'   => [
                'currency' => 'EUR',
            ],
        ],
    ],
];