<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use App\Traits\ConvertsToWebp;

class SiteSetting extends Model
{
    use ConvertsToWebp;

    protected $fillable = [
        'brand_name',
        'logo_light_path',
        'logo_dark_path',
        'color_light_bg',
        'color_dark_bg',
        'color_accent',
        'currency',
        'fx_usd_per_eur',
        'discord_url', // campo DB
    ];

    protected $casts = [
        'fx_usd_per_eur' => 'float',
    ];

    protected static function booted()
    {
        static::saved(function (self $m) {
            if ($m->logo_light_path) {
                $m->toWebp('public', $m->logo_light_path, 75);
            }
            if ($m->logo_dark_path) {
                $m->toWebp('public', $m->logo_dark_path, 75);
            }
        });
    }

    // Preferisci automaticamente la versione .webp se esiste
    public function getLogoLightUrlAttribute(): ?string
    {
        return $this->preferWebp($this->logo_light_path);
    }

    public function getLogoDarkUrlAttribute(): ?string
    {
        return $this->preferWebp($this->logo_dark_path);
    }

    private function preferWebp(?string $path): ?string
    {
        if (!$path) return null;
        $webp = preg_replace('/\.(jpe?g|png|gif|bmp)$/i', '.webp', $path);
        if ($webp && Storage::disk('public')->exists($webp)) {
            return Storage::disk('public')->url($webp);
        }
        return Storage::disk('public')->url($path);
    }
}