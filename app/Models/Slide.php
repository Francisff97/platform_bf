<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Slide extends Model {
  protected $fillable=['title','subtitle','image_path','cta_label','cta_url','sort_order','is_active'];
}
