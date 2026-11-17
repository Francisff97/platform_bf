<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Support\Facades\Storage;
use App\Traits\ConvertsToWebp;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles, ConvertsToWebp;

    protected $fillable = [
        'name','email','password','avatar_path','role',
    ];

    protected $hidden = ['password','remember_token'];
    protected $casts  = ['email_verified_at' => 'datetime','password' => 'hashed'];

    protected static function booted()
    {
        static::saved(function (self $m) {
            if ($m->avatar_path) {
                $m->toWebp('public', $m->avatar_path, 75);
            }
        });
    }

    public function getAvatarUrlAttribute(): ?string
    {
        $path = $this->avatar_path;
        if (!$path) return null;

        $webp = preg_replace('/\.(jpe?g|png|gif|bmp)$/i', '.webp', $path);
        if ($webp && Storage::disk('public')->exists($webp)) {
            return Storage::disk('public')->url($webp);
        }
        return Storage::disk('public')->url($path);
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
}