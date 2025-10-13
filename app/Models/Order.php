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
        'provider_response' => 'array',
    ];

    // === Relazioni base (retrocompat pack/coach singolo) ===
    public function user()  { return $this->belongsTo(User::class); }
    public function pack()  { return $this->belongsTo(Pack::class, 'pack_id'); }
    public function coach() { return $this->belongsTo(Coach::class, 'coach_id'); }

    // === Accessors normalizzati (array) ===
    public function getPackIdsAttribute(): array
    {
        $v = $this->getRawOriginal('pack_id');
        if ($v === null) return [];
        if (is_string($v)) {
            $d = json_decode($v, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($d)) return $this->normalizeIdArray($d);
            if (is_numeric($v)) return [ (int)$v ];
        }
        if (is_array($v))     return $this->normalizeIdArray($v);
        if (is_numeric($v))   return [ (int)$v ];
        return [];
    }

    public function getCoachIdsAttribute(): array
    {
        $v = $this->getRawOriginal('coach_id');
        if ($v === null) return [];
        if (is_string($v)) {
            $d = json_decode($v, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($d)) return $this->normalizeIdArray($d);
            if (is_numeric($v)) return [ (int)$v ];
        }
        if (is_array($v))     return $this->normalizeIdArray($v);
        if (is_numeric($v))   return [ (int)$v ];
        return [];
    }

    // === Mutators (int|array|null) ===
    public function setPackIdAttribute($value): void  { $this->attributes['pack_id']  = $this->prepareIdForStorage($value); }
    public function setCoachIdAttribute($value): void { $this->attributes['coach_id'] = $this->prepareIdForStorage($value); }

    // === Helpers ===
    protected function normalizeIdArray(array $arr): array
    {
        $out = array_values(array_filter(array_map('intval', $arr), fn($v)=>$v>0));
        return array_values(array_unique($out));
    }

    protected function prepareIdForStorage($value)
    {
        if ($value === null || $value === []) return null;
        if (is_array($value)) {
            $n = $this->normalizeIdArray($value);
            if (!$n) return null;
            return count($n) === 1 ? (int)$n[0] : json_encode(array_values($n));
        }
        if (is_numeric($value)) return (int)$value;
        if (is_string($value)) {
            $d = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($d)) {
                return $this->prepareIdForStorage($d);
            }
        }
        return null;
    }

    // Convenienza
    public function packModels()  { $ids = $this->pack_ids;  return $ids ? Pack::whereIn('id',$ids)->get()  : collect([]); }
    public function coachModels() { $ids = $this->coach_ids; return $ids ? Coach::whereIn('id',$ids)->get() : collect([]); }
}
