<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use App\Traits\ConvertsToWebp;

class AboutSection extends Model
{
    use ConvertsToWebp;

    protected $fillable = [
        'layout','title','body','image_path','featured','is_active','position',
    ];

    protected $casts = [
        'featured'  => 'bool',
        'is_active' => 'bool',
        'position'  => 'int',
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

    public function scopeActive($q){ return $q->where('is_active', true); }
    public function scopeOrdered($q){ return $q->orderBy('position')->orderBy('id'); }
    public function scopeFeatured($q){ return $q->active()->where('featured', true); }

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