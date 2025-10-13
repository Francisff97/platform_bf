<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'user_id',
        'pack_id',          // TEXT: può contenere "23" oppure "[23,41]"
        'coach_id',         // TEXT: idem
        'amount_cents',
        'currency',
        'status',
        'provider',
        'provider_order_id',
        'stripe_session_id',
        'items',
        'meta',
        'provider_response',
    ];

    protected $casts = [
        'amount_cents'      => 'integer',
        'items'             => 'array',
        'meta'              => 'array',
        'provider_response' => 'array',
    ];

    // ---------------- Relations (compat scalar) ----------------
    public function user()  { return $this->belongsTo(User::class); }
    public function pack()  { return $this->belongsTo(Pack::class,  'pack_id'); }
    public function coach() { return $this->belongsTo(Coach::class, 'coach_id'); }

    // ---------------- Accessors: array sempre ----------------
    public function getPackIdsAttribute(): array
    {
        $v = $this->getRawOriginal('pack_id');
        if ($v === null) return [];
        if (is_array($v)) return $this->normalizeIdArray($v);
        if (is_numeric($v)) return [(int)$v];
        if (is_string($v)) {
            $dec = json_decode($v, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($dec)) {
                return $this->normalizeIdArray($dec);
            }
            if (is_numeric($v)) return [(int)$v];
        }
        return [];
    }

    public function getCoachIdsAttribute(): array
    {
        $v = $this->getRawOriginal('coach_id');
        if ($v === null) return [];
        if (is_array($v)) return $this->normalizeIdArray($v);
        if (is_numeric($v)) return [(int)$v];
        if (is_string($v)) {
            $dec = json_decode($v, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($dec)) {
                return $this->normalizeIdArray($dec);
            }
            if (is_numeric($v)) return [(int)$v];
        }
        return [];
    }

    // ---------------- Mutators: accettano null|int|array ----------------
    public function setPackIdAttribute($value): void
    {
        $this->attributes['pack_id'] = $this->prepareIdForStorage($value);
    }
    public function setCoachIdAttribute($value): void
    {
        $this->attributes['coach_id'] = $this->prepareIdForStorage($value);
    }

    // ---------------- Helpers ----------------
    protected function normalizeIdArray(array $arr): array
    {
        $out = array_map('intval', $arr);
        $out = array_filter($out, fn($v) => $v > 0);
        return array_values(array_unique($out));
    }

    protected function prepareIdForStorage($value)
    {
        if ($value === null || $value === []) return null;

        if (is_array($value)) {
            $norm = $this->normalizeIdArray($value);
            if (!$norm) return null;
            if (count($norm) === 1) return (string)$norm[0];        // scalar compat
            return json_encode(array_values($norm));                 // array JSON
        }

        if (is_numeric($value)) return (string) ((int)$value);       // scalar compat

        if (is_string($value)) {
            $dec = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($dec)) {
                return $this->prepareIdForStorage($dec);
            }
        }

        return null;
    }

    // Comodi per caricare più modelli
    public function packModels()
    {
        $ids = $this->pack_ids;
        return $ids ? Pack::whereIn('id', $ids)->get() : collect();
    }
    public function coachModels()
    {
        $ids = $this->coach_ids;
        return $ids ? Coach::whereIn('id', $ids)->get() : collect();
    }
}