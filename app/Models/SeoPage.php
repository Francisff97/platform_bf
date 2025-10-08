<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SeoPage extends Model
{
    protected $fillable = ['route_name','path','meta_title','meta_description','og_image_path'];
}
