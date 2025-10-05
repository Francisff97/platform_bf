<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteSetting extends Model
{
    protected $fillable = [
        'brand_name',
        'logo_light_path',
        'logo_dark_path',
        'color_light_bg',
        'color_dark_bg',
        'color_accent',
        'currency',
        'fx_usd_per_eur',
        'discord_url', // 👈 nome campo nel DB
    ];

    protected $casts = [
        'fx_usd_per_eur' => 'float',
    ];
}