<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coach;
use App\Models\CoachPrice;
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
            'name'        => 'required|string|max:180',
            'slug'        => 'nullable|alpha_dash|unique:coaches,slug',
            'team'        => 'nullable|string|max:180',
            'image'       => 'nullable|image|max:4096',
            'skills'      => 'nullable|string', // CSV o testo
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
            $payload['image_path'] = $r->file('image')->store('coaches', 'public');
        }

        // crea il coach
        $coach = Coach::create($payload);

        // crea le varianti prezzo
        if (!empty($data['prices'])) {
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

        return redirect()->route('admin.coaches.index')->with('success','Coach creato.');
    }

    public function edit(Coach $coach)
    {
        // carica anche i prezzi per mostrarli nel form
        $coach->load('prices');
        return view('admin.coaches.edit', compact('coach'));
    }

    public function update(Request $r, Coach $coach)
    {
        $data = $r->validate([
            'name'        => 'required|string|max:180',
            'slug'        => "required|alpha_dash|unique:coaches,slug,{$coach->id}",
            'team'        => 'nullable|string|max:180',
            'image'       => 'nullable|image|max:4096',
            'skills'      => 'nullable|string',
            // prezzi (varianti)
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
            if ($coach->image_path) {
                Storage::disk('public')->delete($coach->image_path);
            }
            $coach->image_path = $r->file('image')->store('coaches','public');
        }

        $coach->save();

        // reset & ricrea prezzi (semplice e sicuro)
        if ($r->filled('prices')) {
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
        } else {
            // se il form non passa nulla, opzionale: lascia i vecchi o pulisci
            // $coach->prices()->delete();
        }

        return redirect()->route('admin.coaches.index')->with('success','Coach aggiornato.');
    }

    public function destroy(Coach $coach)
    {
        if ($coach->image_path) {
            Storage::disk('public')->delete($coach->image_path);
        }
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
