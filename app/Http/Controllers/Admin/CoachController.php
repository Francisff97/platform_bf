<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coach;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CoachController extends Controller
{
    public function index()
    {
        $coaches = Coach::latest()->paginate(20);
        return view('admin.coaches.index', compact('coaches'));
    }

    public function create()
    {
        return view('admin.coaches.create');
    }

    public function store(Request $r)
    {
        $data = $r->validate([
            'name'                 => 'required|string|max:180',
            'slug'                 => 'nullable|alpha_dash|unique:coaches,slug',
            'team'                 => 'nullable|string|max:180',
            'image'                => 'nullable|image|max:4096',
            'skills'               => 'nullable|string', // CSV o testo
            // prezzi (varianti)
            'prices'                  => 'nullable|array',
            'prices.*.duration'       => 'required_with:prices.*.price_cents|string|max:50',
            'prices.*.price_cents'    => 'required_with:prices.*.duration|integer|min:0',
            'prices.*.currency'       => 'nullable|string|size:3',
        ]);

        $payload = [
            'name'   => $data['name'],
            'slug'   => $data['slug'] ?? Str::slug($data['name']).'-'.Str::random(5),
            'team'   => $data['team'] ?? null,
            'skills' => $this->skillsToArray($data['skills'] ?? null),
        ];

        if ($r->hasFile('image')) {
            $path = $r->file('image')->store('coaches', 'public');
            $payload['image_path'] = $path;

            // indicizza su SEO Media (se presente)
            if (class_exists(\App\Support\MediaIngestor::class)) {
                \App\Support\MediaIngestor::ingest('public', $path, [
                    'alt'  => $payload['name'],
                    'lazy' => true,
                ]);
            }
        }

        // crea il coach
        $coach = Coach::create($payload);

        // crea le varianti prezzo (se arrivate)
        if (!empty($data['prices']) && is_array($data['prices'])) {
            foreach ($data['prices'] as $p) {
                if (!empty($p['duration']) && isset($p['price_cents'])) {
                    $coach->prices()->create([
                        'duration'    => $p['duration'],
                        'price_cents' => (int)$p['price_cents'],
                        'currency'    => $p['currency'] ?? 'EUR',
                    ]);
                }
            }
        }

        return redirect()->route('admin.coaches.index')->with('success', 'Coach creato.');
    }

    public function edit(Coach $coach)
    {
        $coach->load('prices');
        return view('admin.coaches.edit', compact('coach'));
    }

    public function update(Request $r, Coach $coach)
    {
        $data = $r->validate([
            'name'                 => 'required|string|max:180',
            'slug'                 => "required|alpha_dash|unique:coaches,slug,{$coach->id}",
            'team'                 => 'nullable|string|max:180',
            'image'                => 'nullable|image|max:4096',
            'skills'               => 'nullable|string',
            'prices'                  => 'nullable|array',
            'prices.*.duration'       => 'required_with:prices.*.price_cents|string|max:50',
            'prices.*.price_cents'    => 'required_with:prices.*.duration|integer|min:0',
            'prices.*.currency'       => 'nullable|string|size:3',
        ]);

        $coach->name   = $data['name'];
        $coach->slug   = $data['slug'];
        $coach->team   = $data['team'] ?? null;
        $coach->skills = $this->skillsToArray($data['skills'] ?? null);

        if ($r->hasFile('image')) {
            // elimina vecchio file (se esiste)
            if ($coach->image_path) {
                Storage::disk('public')->delete($coach->image_path);
            }
            $path = $r->file('image')->store('coaches','public');
            $coach->image_path = $path;

            // re-indicizza su SEO Media
            if (class_exists(\App\Support\MediaIngestor::class)) {
                \App\Support\MediaIngestor::ingest('public', $path, [
                    'alt'  => $coach->name,
                    'lazy' => true,
                ]);
            }
        }

        $coach->save();

        // reset & ricrea prezzi se presenti
        if ($r->filled('prices') && is_array($data['prices'])) {
            $coach->prices()->delete();
            foreach ($data['prices'] as $p) {
                if (!empty($p['duration']) && isset($p['price_cents'])) {
                    $coach->prices()->create([
                        'duration'    => $p['duration'],
                        'price_cents' => (int)$p['price_cents'],
                        'currency'    => $p['currency'] ?? 'EUR',
                    ]);
                }
            }
        }

        return redirect()->route('admin.coaches.index')->with('success', 'Coach aggiornato.');
    }

    public function destroy(Coach $coach)
    {
        if ($coach->image_path) {
            Storage::disk('public')->delete($coach->image_path);
        }
        $coach->prices()->delete();
        $coach->delete();

        return back()->with('success','Coach eliminato.');
    }

    private function skillsToArray(?string $csv): ?array
    {
        if (!$csv) return null;
        return collect(preg_split('/[,;\n]+/', $csv))
            ->map(fn($s)=>trim($s))
            ->filter()
            ->values()
            ->all();
    }
}
