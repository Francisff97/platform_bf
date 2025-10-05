<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Coach extends Model
{
    use HasFactory;

    protected $fillable = ['name','slug','team','image_path','skills'];

    protected $casts = [
        'skills' => 'array',
    ];
    public function prices() {
        return $this->hasMany(CoachPrice::class);
    }
    public function tutorials()
{
    return $this->morphMany(\App\Models\Tutorial::class, 'tutorialable')->orderBy('sort_order');
}
    

    // se vuoi future relazioni (es. packs della persona) le aggiungi qui
}
