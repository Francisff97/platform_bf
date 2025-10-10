<?php
namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    protected $fillable = [
        'code','type','value','value_cents','is_active',
        'min_order_cents','starts_at','ends_at','usage_count','max_uses'
    ];
    protected $casts = [
        'is_active' => 'boolean',
        'starts_at' => 'datetime',
        'ends_at'   => 'datetime',
    ];

    public function isCurrentlyValid(int $orderCents): bool
    {
        if (!$this->is_active) return false;
        if ($this->max_uses && $this->usage_count >= $this->max_uses) return false;
        if ($this->starts_at && now()->lt($this->starts_at)) return false;
        if ($this->ends_at && now()->gt($this->ends_at)) return false;
        if ($orderCents < (int)$this->min_order_cents) return false;

        if ($this->type === 'percent') {
            return (int)$this->value > 0 && (int)$this->value <= 100;
        }
        if ($this->type === 'fixed') {
            return (int)$this->value_cents > 0;
        }
        return false;
    }

    public function discountFor(int $orderCents): int
    {
        if (!$this->isCurrentlyValid($orderCents)) return 0;

        if ($this->type === 'percent') {
            return (int) round($orderCents * ((int)$this->value / 100));
        }
        if ($this->type === 'fixed') {
            return min((int)$this->value_cents, $orderCents);
        }
        return 0;
    }
}