<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'user_id',
        'pack_id',          // scalar legacy
        'pack_id_json',     // array JSON
        'coach_id',         // scalar legacy
        'coach_id_json',    // array JSON
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
        'provider_response' => 'array',
        'pack_id_json'      => 'array',
        'coach_id_json'     => 'array',
    ];

    // Relazioni legacy (singolo)
    public function user()  { return $this->belongsTo(User::class); }
    public function pack()  { return $this->belongsTo(Pack::class, 'pack_id'); }
    public function coach() { return $this->belongsTo(Coach::class, 'coach_id'); }

    // Accessor normalizzati (preferisci *_json, altrimenti scalar)
    public function getPackIdsAttribute(): array
    {
        if (!empty($this->pack_id_json)) {
            return array_values(array_unique(array_map('intval', (array)$this->pack_id_json)));
        }
        return $this->pack_id ? [(int)$this->pack_id] : [];
    }

    public function getCoachIdsAttribute(): array
    {
        if (!empty($this->coach_id_json)) {
            return array_values(array_unique(array_map('intval', (array)$this->coach_id_json)));
        }
        return $this->coach_id ? [(int)$this->coach_id] : [];
    }

    // Convenienza
    public function packModels()
    {
        $ids = $this->pack_ids;
        return $ids ? Pack::whereIn('id', $ids)->get() : collect([]);
    }

    public function coachModels()
    {
        $ids = $this->coach_ids;
        return $ids ? Coach::whereIn('id', $ids)->get() : collect([]);
    }
}