<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Hero;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class HeroController extends Controller
{
    public function index()
    {
        $heroes = Hero::orderByDesc('created_at')->paginate(15);
        return view('admin.heroes.index', compact('heroes'));
    }

    public function create()
    {
        return view('admin.heroes.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'      => 'nullable|string|max:180',
            'subtitle'   => 'nullable|string|max:500',
            'image'      => 'nullable|image|max:8192',
            'cta_label'  => 'nullable|string|max:80',
            'cta_url'    => 'nullable|url',
            'page'       => 'nullable|string|max:100',
            'sort_order' => 'nullable|integer',
            // 👇 qui accettiamo "70vh" o "480px"
            'height_css' => ['nullable','regex:/^\d+(vh|px)$/i'],
            'full_bleed' => 'sometimes|boolean',
            'is_active'  => 'sometimes|boolean',
        ]);

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('heroes','public');
        }

        // 👇 gestisci checkbox in modo robusto
        $data['full_bleed'] = $request->boolean('full_bleed'); // true/false anche se unchecked
        $data['is_active']  = $request->boolean('is_active');

        Hero::create($data);

        return redirect()->route('admin.heroes.index')->with('success', 'Hero creato');
    }

    public function edit(Hero $hero)
    {
        return view('admin.heroes.edit', compact('hero'));
    }

    public function update(Request $request, Hero $hero)
    {
        $data = $request->validate([
            'title'      => 'nullable|string|max:180',
            'subtitle'   => 'nullable|string|max:500',
            'image'      => 'nullable|image|max:8192',
            'cta_label'  => 'nullable|string|max:80',
            'cta_url'    => 'nullable|url',
            'page'       => 'nullable|string|max:100',
            'sort_order' => 'nullable|integer',
            // 👇 usa SEMPRE height_css, non "height"
            'height_css' => ['nullable','regex:/^\d+(vh|px)$/i'],
            'full_bleed' => 'sometimes|boolean',
            'is_active'  => 'sometimes|boolean',
        ]);

        if ($request->hasFile('image')) {
            if ($hero->image_path) {
                Storage::disk('public')->delete($hero->image_path);
            }
            $data['image_path'] = $request->file('image')->store('heroes','public');
        }

        $data['full_bleed'] = $request->boolean('full_bleed');
        $data['is_active']  = $request->boolean('is_active');

        $hero->update($data);

        return back()->with('success', 'Hero aggiornato');
    }

    public function destroy(Hero $hero)
    {
        if ($hero->image_path) {
            Storage::disk('public')->delete($hero->image_path);
        }
        $hero->delete();
        return back()->with('success','Hero eliminato');
    }
}
