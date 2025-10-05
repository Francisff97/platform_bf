<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
    'user_id',
    'pack_id',
    'coach_id',
    'amount_cents',
    'currency',
    'status',
    'provider',
    'provider_order_id',
    'stripe_session_id',
    'meta',
    'provider_response',
];

    protected $casts = [
        'amount_cents'      => 'integer',
        'meta'              => 'array',
        'provider_response' => 'array', // se salvi JSON; se salvi raw string, togli o metti 'string'
    ];

    // Relazioni
    public function user()  { return $this->belongsTo(User::class); }
    public function pack()  { return $this->belongsTo(Pack::class); }
    public function coach() { return $this->belongsTo(Coach::class); }
}