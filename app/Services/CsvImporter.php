<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use League\Csv\Reader;
use League\Csv\Statement;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;

class CsvImporter
{
    public function import(string $entity, string $file, array $mapping): array
    {
        $cfg = config("csv_import.entities.$entity");
        abort_unless($cfg, 404, 'Entity not configured');

        $modelClass = $cfg['model'];
        /** @var Model $model */
        $model = new $modelClass;

        $uniqueBy = $cfg['unique_by'] ?? [];
        $fillable = $cfg['fillable'] ?? [];
        $rules    = $cfg['rules'] ?? [];
        $defaults = $cfg['defaults'] ?? [];

        $chunk = (int) config('csv_import.chunk', 1000);

        $csvPath = Storage::path($file);
        $csv = Reader::createFromPath($csvPath, 'r');
        $csv->setHeaderOffset(0);

        $ok = $fail = $updated = 0;
        $stmt = (new Statement());

        $records = $stmt->process($csv);
        $buffer = [];

        foreach ($records as $row) {
            $data = $this->mapRow($row, $mapping, $fillable, $defaults);
            if (empty($data)) { $fail++; continue; }

            // Validazione
            $v = Validator::make($data, $rules);
            if ($v->fails()) { $fail++; continue; }

            $buffer[] = $data;

            if (count($buffer) >= $chunk) {
                [$a, $b] = $this->flush($modelClass, $buffer, $uniqueBy);
                $ok      += $a;
                $updated += $b;
                $buffer   = [];
            }
        }

        if ($buffer) {
            [$a, $b] = $this->flush($modelClass, $buffer, $uniqueBy);
            $ok      += $a;
            $updated += $b;
        }

        return [$ok, $fail, $updated];
    }

    private function mapRow(array $row, array $mapping, array $fillable, array $defaults): array
    {
        $out = $defaults;

        foreach ($mapping as $csvHeader => $field) {
            if (!$field) continue; // non mappare
            $val = Arr::get($row, $csvHeader);

            // Trasformazioni
            $transform = $fillable[$field]['transform'] ?? null;
            if ($transform) {
                $val = $this->applyTransform($transform, $val);
            }
            $out[$field] = $val;
        }

        return array_filter($out, fn($v) => !is_null($v) && $v !== '');
    }

    private function applyTransform(string $transform, $value)
    {
        switch ($transform) {
            case 'int':
                return (int) (is_numeric($value) ? $value : 0);
            case 'hash_if_plain':
                if (!$value) return null;
                // se sembra già hashato, lascialo
                return Str::startsWith($value, '$2y$') ? $value : bcrypt($value);
            case 'json_or_csv_array':
                if (!$value) return [];
                $value = trim($value);
                if (Str::startsWith($value, '[')) {
                    // JSON
                    $arr = json_decode($value, true);
                    return is_array($arr) ? $arr : [];
                }
                // CSV semplice
                return array_values(array_filter(array_map('trim', explode(',', $value))));
            default:
                return $value;
        }
    }

    /**
     * Esegue upsert. Ritorna [inserted, updated]
     */
    private function flush(string $modelClass, array $buffer, array $uniqueBy): array
    {
        if (empty($buffer)) return [0,0];

        // upsert ritorna niente. Per contare updated/inserted,
        // approccio: tenta upsert e poi ricalcola quanti esistevano già.
        $model = new $modelClass;
        $table = $model->getTable();

        // ids già esistenti (in base a unique_by)
        $existing = 0;
        if (!empty($uniqueBy)) {
            // costruisci una chiave hash delle colonne unique per contare i già presenti
            $keys = [];
            foreach ($buffer as $row) {
                $k = [];
                foreach ($uniqueBy as $col) $k[] = $row[$col] ?? null;
                $keys[] = implode('|', $k);
            }
            // query per esistenti
            $q = $modelClass::query();
            foreach ($uniqueBy as $col) {
                $vals = array_unique(array_column($buffer, $col));
                $q->whereIn($col, $vals);
            }
            $existing = $q->count();
        }

        $modelClass::upsert($buffer, $uniqueBy, array_keys($buffer[0] ?? []));

        $inserted = max(count($buffer) - $existing, 0);
        $updated  = min($existing, count($buffer));
        return [$inserted, $updated];
    }
}