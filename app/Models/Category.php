<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Category extends Model
{
    use HasFactory;

    protected $fillable = ['name','slug','color'];

    protected static function booted()
    {
        static::saving(function($cat){
            if (empty($cat->slug)) $cat->slug = Str::slug($cat->name);
        });
    }

    public function packs()
    {
        return $this->hasMany(Pack::class);
    }
}