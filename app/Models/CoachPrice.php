<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CoachPrice extends Model
{
    protected $fillable = ['coach_id','duration','price_cents','currency'];

    public function coach() {
        return $this->belongsTo(Coach::class);
    }
}
