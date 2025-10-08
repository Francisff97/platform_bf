<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ServiceController extends Controller
{
    public function index()
    {
        $services = Service::orderBy('order')->paginate(50);
        return view('admin.services.index', compact('services'));
    }

    public function create()
    {
        return view('admin.services.create');
    }

    public function store(Request $r)
    {
        $data = $r->validate([
            'name'    => ['required','max:160'],
            'slug'    => ['nullable','alpha_dash','unique:services,slug'],
            'excerpt' => ['nullable','max:255'],
            'body'    => ['nullable'],
            'order'   => ['nullable','integer','min:0'],
            'status'  => ['required','in:draft,published'],
            'image'   => ['nullable','image','mimes:jpg,jpeg,png,webp,avif','max:4096'],
        ]);

        $data['slug'] = $data['slug'] ?? Str::slug($data['name']);

        // Salva l'immagine (se caricata)
        if ($r->hasFile('image')) {
            $path = $r->file('image')->store('services', 'public'); // -> storage/app/public/services/...
            $data['image_path'] = $path;

            // Ingestion SEO (alt/lazy registry)
            if (class_exists(\App\Support\MediaIngestor::class)) {
                \App\Support\MediaIngestor::ingest('public', $path);
            }
        }

        Service::create($data);

        return redirect()
            ->route('admin.services.index')
            ->with('success', 'Service creato.');
    }

    public function edit(Service $service)
    {
        return view('admin.services.edit', compact('service'));
    }

    public function update(Request $r, Service $service)
    {
        $data = $r->validate([
            'name'    => ['required','max:160'],
            'slug'    => ["required",'alpha_dash',"unique:services,slug,{$service->id}"],
            'excerpt' => ['nullable','max:255'],
            'body'    => ['nullable'],
            'order'   => ['nullable','integer','min:0'],
            'status'  => ['required','in:draft,published'],
            'image'   => ['nullable','image','mimes:jpg,jpeg,png,webp,avif','max:4096'],
        ]);

        // Salva nuova immagine se presente
        if ($r->hasFile('image')) {
            $path = $r->file('image')->store('services', 'public');
            $data['image_path'] = $path;

            // (opzionale) rimuovi vecchia immagine se vuoi
            // if ($service->image_path && Storage::disk('public')->exists($service->image_path)) {
            //     Storage::disk('public')->delete($service->image_path);
            // }

            // ingestion SEO
            if (class_exists(\App\Support\MediaIngestor::class)) {
                \App\Support\MediaIngestor::ingest('public', $path);
            }
        }

        $service->update($data);

        return back()->with('success', 'Service aggiornato.');
    }

    public function destroy(Service $service)
    {
        // (opzionale) elimina anche file
        // if ($service->image_path && Storage::disk('public')->exists($service->image_path)) {
        //     Storage::disk('public')->delete($service->image_path);
        // }

        $service->delete();
        return back()->with('success', 'Service eliminato.');
    }
}
