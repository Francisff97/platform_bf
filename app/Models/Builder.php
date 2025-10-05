<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Builder extends Model
{
    use HasFactory;

    protected $fillable = ['name','slug','team','image_path','skills','description'];
protected $casts = ['skills'=>'array'];

    public function packs()
    {
        return $this->hasMany(\App\Models\Pack::class);
    }
}
