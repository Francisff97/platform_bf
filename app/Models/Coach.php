<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Storage;
use App\Traits\ConvertsToWebp;

class Coach extends Model
{
    use HasFactory, ConvertsToWebp;

    protected $fillable = ['name','slug','team','image_path','skills'];
    protected $casts = ['skills' => 'array'];

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

    public function prices()  { return $this->hasMany(CoachPrice::class); }

    public function tutorials()
    {
        return $this->morphMany(\App\Models\Tutorial::class, 'tutorialable')->orderBy('sort_order');
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