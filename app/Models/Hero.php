<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Hero extends Model {
  protected $fillable = [
    'title','subtitle','image_path','cta_label','cta_url',
    'height_css','full_bleed','page'// 👈 nuovi
  ];
  protected $casts = [
    'full_bleed' => 'boolean',
  ];
}
