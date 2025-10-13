<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'user_id',
        'pack_id',          // legacy scalar (int)
        'coach_id',         // legacy scalar (int)
        'pack_id_json',     // nuovo JSON (array di int)
        'coach_id_json',    // nuovo JSON (array di int)
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
        'pack_id_json'      => 'array',
        'coach_id_json'     => 'array',
    ];

    // ------------ Relazioni (legacy per compat) ------------
    public function user()  { return $this->belongsTo(User::class); }
    public function pack()  { return $this->belongsTo(Pack::class, 'pack_id'); }
    public function coach() { return $this->belongsTo(Coach::class, 'coach_id'); }

    // ------------ Accessor comodi: sempre array normalizzati ------------
    public function getPackIdsAttribute(): array
    {
        $arr = is_array($this->pack_id_json) ? $this->pack_id_json : [];
        if ($this->pack_id) $arr[] = (int)$this->pack_id;
        return $this->normalizeIdArray($arr);
    }

    public function getCoachIdsAttribute(): array
    {
        $arr = is_array($this->coach_id_json) ? $this->coach_id_json : [];
        if ($this->coach_id) $arr[] = (int)$this->coach_id;
        return $this->normalizeIdArray($arr);
    }

    // ------------ Mutators (accettano int|array|null) ------------
    // setPackIds()/setCoachIds() sono metodi helper *non* magici: chiamali tu nel controller
    public function setPackIds(array|int|null $value): void
    {
        [$legacy, $json] = $this->splitLegacyVsJson($value);
        $this->attributes['pack_id'] = $legacy;
        $this->attributes['pack_id_json'] = $json;
    }

    public function setCoachIds(array|int|null $value): void
    {
        [$legacy, $json] = $this->splitLegacyVsJson($value);
        $this->attributes['coach_id'] = $legacy;
        $this->attributes['coach_id_json'] = $json;
    }

    // ------------ Helpers ------------
    protected function normalizeIdArray(array $arr): array
    {
        $out = array_values(array_filter(array_map('intval', $arr), fn($v) => $v > 0));
        return array_values(array_unique($out));
    }

    /** Se 0/1 elemento => legacy int; se >1 => legacy NULL + JSON array */
    protected function splitLegacyVsJson(array|int|null $value): array
    {
        if ($value === null) return [null, null];

        if (is_int($value)) return [$value, null];

        $ids = $this->normalizeIdArray($value);
        if (count($ids) === 0) return [null, null];
        if (count($ids) === 1) return [$ids[0], null];
        return [null, $ids]; // JSON only
    }

    // Convenience
    public function packModels()
    {
        $ids = $this->pack_ids;
        return empty($ids) ? collect([]) : Pack::whereIn('id', $ids)->get();
    }
    public function coachModels()
    {
        $ids = $this->coach_ids;
        return empty($ids) ? collect([]) : Coach::whereIn('id', $ids)->get();
    }
}