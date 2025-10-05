<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar_path',
        'role',
    ];
    public function getAvatarUrlAttribute()
{
  return $this->avatar_path
    ? \Illuminate\Support\Facades\Storage::url($this->avatar_path)
    : null;
}
public function hasPurchasedPack(int $packId): bool
{
    return \App\Models\Order::query()
        ->where('user_id', $this->id)
        ->where('status', 'paid')
        ->where('pack_id', $packId)
        ->exists();
}

public function hasPurchasedCoach(int $coachId): bool
{
    return \App\Models\Order::query()
        ->where('user_id', $this->id)
        ->where('status', 'paid')
        ->where('coach_id', $coachId)
        ->exists();
}

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];
    
    
}
