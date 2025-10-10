<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use App\Traits\ConvertsToWebp;

class Slide extends Model
{
    use ConvertsToWebp;

    protected $fillable = [
        'title','subtitle','image_path','cta_label','cta_url','sort_order','is_active'
    ];

    protected static function booted()
    {
        static::saved(function (self $m) {
            if ($m->image_path) {
                $m->toWebp('public', $m->image_path, 75);
            }
        });
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->preferWebp($this->image_path);
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