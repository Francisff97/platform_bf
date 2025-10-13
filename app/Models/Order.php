<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

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
        'items',
        'meta',
        'provider_response',
    ];

    /**
     * Casts
     *
     * - pack_id e coach_id vengono gestiti tramite accessor/mutator custom (vedi sotto).
     * - items/meta/provider_response sono array (JSON columns).
     */
    protected $casts = [
        'amount_cents'      => 'integer',
        'items'             => 'array',
        'meta'              => 'array',
        'provider_response' => 'array',
        // NOTA: non mettere qui 'pack_id'/'coach_id' perché vogliamo comportamento custom
    ];

    // ----------------------
    // RELAZIONI
    // ----------------------
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Ritorna il primo pack (se pack_id è scalar). Comportamento compatibile.
     * NOTA: se pack_id è array, ->pack() non è molto utile; usa ->packModels()
     */
    public function pack()
    {
        return $this->belongsTo(Pack::class, 'pack_id');
    }

    public function coach()
    {
        return $this->belongsTo(Coach::class, 'coach_id');
    }

    // ----------------------
    // ACCESSORS: pack_ids / coach_ids sempre come array normalizzato
    // ----------------------
    public function getPackIdsAttribute(): array
    {
        $v = $this->getRawOriginal('pack_id'); // prende il valore raw dal DB
        if ($v === null) return [];
        // Se è JSON memorizzato come stringa -> decode
        if (is_string($v)) {
            $decoded = json_decode($v, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $this->normalizeIdArray($decoded);
            }
            // altrimenti può essere uno scalar salvato come "1" o "1" casted
            if (is_numeric($v)) return [ (int) $v ];
        }
        // Se è array già (driver PDO potrebbe restituire array per json column)
        if (is_array($v)) return $this->normalizeIdArray($v);

        // Se è scalar numerico
        if (is_numeric($v)) return [ (int) $v ];

        return [];
    }

    public function getCoachIdsAttribute(): array
    {
        $v = $this->getRawOriginal('coach_id');
        if ($v === null) return [];
        if (is_string($v)) {
            $decoded = json_decode($v, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $this->normalizeIdArray($decoded);
            }
            if (is_numeric($v)) return [ (int) $v ];
        }
        if (is_array($v)) return $this->normalizeIdArray($v);
        if (is_numeric($v)) return [ (int) $v ];
        return [];
    }

    // ----------------------
    // MUTATORS: setPackIdAttribute / setCoachIdAttribute
    // Accettano: null | int | array
    // - se null -> salva null
    // - se int scalar -> salva valore scalare (retrocompatibilità)
    // - se array -> salva JSON (array unico, deduplicato, int)
    // ----------------------
    public function setPackIdAttribute($value): void
    {
        $this->attributes['pack_id'] = $this->prepareIdForStorage($value);
    }

    public function setCoachIdAttribute($value): void
    {
        $this->attributes['coach_id'] = $this->prepareIdForStorage($value);
    }

    // ----------------------
    // HELPERS
    // ----------------------
    /**
     * Normalizza array di id (int, de-dup)
     */
    protected function normalizeIdArray(array $arr): array
    {
        $out = array_values(array_filter(array_map('intval', $arr), function ($v) {
            return $v > 0;
        }));
        return array_values(array_unique($out));
    }

    /**
     * Prepara valore da salvare nella colonna:
     * - null -> null
     * - int -> int (scalar)
     * - array -> json string
     */
    protected function prepareIdForStorage($value)
    {
        if ($value === null || $value === []) {
            return null;
        }

        if (is_array($value)) {
            $normalized = $this->normalizeIdArray($value);
            if (count($normalized) === 0) return null;
            if (count($normalized) === 1) {
                // retrocompatibilità: salva scalar se c'è un solo elemento
                return (int) $normalized[0];
            }
            return json_encode(array_values($normalized));
        }

        // scalar
        if (is_numeric($value)) {
            return (int) $value;
        }

        // fallback: prova decode se è stringa JSON
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $this->prepareIdForStorage($decoded);
            }
        }

        // se non riconosciuto -> null
        return null;
    }

    // ----------------------
    // Convenience: carica i modelli packs/coaches basati sugli id normalizzati
    // ----------------------
    public function packModels()
    {
        $ids = $this->pack_ids; // usa accessor -> sempre array
        if (empty($ids)) return collect([]);
        return Pack::whereIn('id', $ids)->get();
    }

    public function coachModels()
    {
        $ids = $this->coach_ids;
        if (empty($ids)) return collect([]);
        return Coach::whereIn('id', $ids)->get();
    }
}