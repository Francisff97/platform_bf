<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Pack extends Model
{
    use HasFactory;

    protected $fillable = [
        'title','slug','excerpt','description',
        'image_path',       // 👈 nuovo
        'price_cents','currency',
        'is_featured','status','published_at',
        'category_id',      // 👈 nuovo
        'builder_id',   // 👈 aggiungi questo
      ];

    protected $casts = [
        'is_featured'=>'boolean',
        'published_at'=>'datetime',
    ];

    public function scopePublished($q)
    {
        return $q->where('status','published');
    }
    public function category(){ return $this->belongsTo(Category::class); }
    public function builder(){ return $this->belongsTo(\App\Models\Builder::class); }
    public function tutorials()
{
    return $this->morphMany(\App\Models\Tutorial::class, 'tutorialable')->orderBy('sort_order');
}

}
