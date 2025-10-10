<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Storage;
use App\Traits\ConvertsToWebp;

class Pack extends Model
{
    use HasFactory, ConvertsToWebp;

    protected $fillable = [
        'title','slug','excerpt','description',
        'image_path',
        'price_cents','currency',
        'is_featured','status','published_at',
        'category_id',
        'builder_id',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'published_at'=> 'datetime',
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

    public function scopePublished($q){ return $q->where('status','published'); }
    public function category(){ return $this->belongsTo(Category::class); }
    public function builder(){ return $this->belongsTo(Builder::class); }

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