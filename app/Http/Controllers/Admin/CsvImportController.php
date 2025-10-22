<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Jobs\CsvImportJob;
use League\Csv\Reader;
use League\Csv\Statement;

class CsvImportController extends Controller
{
    public function index()
    {
        $entities = array_keys(config('csv_import.entities', []));
        return view('admin.csv.index', compact('entities'));
    }

    public function upload(Request $r)
    {
        $r->validate([
            'entity' => ['required', 'string', 'in:'.implode(',', array_keys(config('csv_import.entities')))],
            'file'   => ['required', 'file', 'mimes:csv,txt', 'max:20480'], // 20MB
        ]);

        $entity = $r->string('entity');
        $path   = $r->file('file')->store('csv_imports');

        // Leggi header + prime righe
        [$headers, $sample] = $this->readHeadersAndSample($path, 5);

        // Config entity + auto-mapping
        $cfg     = config("csv_import.entities.$entity");
        $fields  = array_keys($cfg['fillable']);
        $mapping = $this->autoMap($headers, $cfg['fillable']);

        return view('admin.csv.map', [
            'entity'  => $entity,
            'file'    => $path,
            'headers' => $headers,
            'fields'  => $cfg['fillable'],
            'mapping' => $mapping,
            'sample'  => $sample,
        ]);
    }

    public function preview(Request $r)
    {
        $r->validate([
            'file' => ['required', 'string'],
        ]);
        [$headers, $sample] = $this->readHeadersAndSample($r->string('file'), 20);
        return response()->json(['headers'=>$headers, 'sample'=>$sample]);
    }

    public function import(Request $r)
    {
        $r->validate([
            'entity'  => ['required', 'string', 'in:'.implode(',', array_keys(config('csv_import.entities')))],
            'file'    => ['required', 'string'],
            'mapping' => ['required','array'],  // es: ["Titolo CSV" => "title", ...]
            'mode'    => ['nullable','in:sync,queue'],
        ]);

        $entity  = $r->string('entity');
        $file    = $r->string('file');
        $mapping = array_filter($r->input('mapping', [])); // rimuove “non mappare”
        $mode    = $r->string('mode') ?: 'queue';

        if ($mode === 'sync') {
            [$ok, $fail, $updated] = app(\App\Services\CsvImporter::class)
                ->import($entity, $file, $mapping);
            return back()->with('success', "Import finito. Creati: $ok, Aggiornati: $updated, Scartati: $fail");
        }

        CsvImportJob::dispatch($entity, $file, $mapping, auth()->id());
        return back()->with('success', 'Import avviato in background. Ti avviseremo al termine.');
    }

    private function readHeadersAndSample(string $path, int $limit = 5): array
    {
        $full = Storage::path($path);
        $csv  = Reader::createFromPath($full, 'r');
        $csv->setHeaderOffset(0);
        $headers = $csv->getHeader();
        $stmt = (new Statement())->limit($limit);
        $records = iterator_to_array($stmt->process($csv));
        return [$headers, $records];
    }

    private function autoMap(array $headers, array $fillable): array
    {
        // prova: match case-insensitive su label o campo
        $map = [];
        foreach ($headers as $h) {
            $cand = null;
            $hLow = mb_strtolower(trim($h));
            foreach ($fillable as $field => $meta) {
                $label = mb_strtolower($meta['label'] ?? $field);
                if ($hLow === mb_strtolower($field) || $hLow === $label) {
                    $cand = $field; break;
                }
            }
            $map[$h] = $cand; // null => non mappare
        }
        return $map;
    }
}