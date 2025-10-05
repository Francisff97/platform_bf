<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tutorial extends Model
{
    protected $fillable = [
        'title','provider','video_url','is_public','sort_order',
    ];

    public function tutorialable()
    {
        return $this->morphTo();
    }
}