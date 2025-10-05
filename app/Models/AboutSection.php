<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AboutSection extends Model
{
    protected $fillable = [
        'layout','title','body','image_path','featured','is_active','position',
    ];

    protected $casts = [
        'featured'  => 'bool',
        'is_active' => 'bool',
        'position'  => 'int',
    ];

    public function scopeActive($q){ return $q->where('is_active', true); }
    public function scopeOrdered($q){ return $q->orderBy('position')->orderBy('id'); }
    public function scopeFeatured($q){ return $q->active()->where('featured', true); }
}