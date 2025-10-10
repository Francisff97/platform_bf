<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrivacySetting extends Model
{
    protected $fillable = [
        'provider',
        'banner_enabled','banner_head_code','banner_body_code',
        'policy_enabled','policy_external','policy_external_url','policy_html',
        'cookies_enabled','cookies_external','cookies_external_url','cookies_html',
        'last_updated_at',
    ];

    // Singleton helper
    public static function singleton(): self
    {
        return static::first() ?? static::create([]);
    }
}