<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AboutSection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AboutSectionController extends Controller
{
    public function index()
    {
        $sections = AboutSection::ordered()->get();
        return view('admin.about.index', compact('sections'));
    }

    public function create()
    {
        $section = new AboutSection(['layout' => 'text', 'position' => (AboutSection::max('position') ?? 0) + 1]);
        return view('admin.about.form', compact('section'));
    }

    public function store(Request $r)
    {
        $data = $this->validated($r);

        if ($r->hasFile('image')) {
            $data['image_path'] = $r->file('image')->store('about', 'public');
        }

        AboutSection::create($data);

        return redirect()->route('admin.about.index')->with('success', 'Section created');
    }

    public function edit(AboutSection $about)
    {
        $section = $about;
        return view('admin.about.form', compact('section'));
    }

    public function update(Request $r, AboutSection $about)
    {
        $data = $this->validated($r);

        if ($r->hasFile('image')) {
            // elimina vecchia (opzionale)
            if ($about->image_path) {
                Storage::disk('public')->delete($about->image_path);
            }
            $data['image_path'] = $r->file('image')->store('about', 'public');
        }

        $about->update($data);

        return redirect()->route('admin.about.index')->with('success', 'Section updated');
    }

    public function destroy(AboutSection $about)
    {
        if ($about->image_path) {
            Storage::disk('public')->delete($about->image_path);
        }
        $about->delete();

        return redirect()->route('admin.about.index')->with('success', 'Section deleted');
    }

    private function validated(Request $r): array
    {
        // Supporta sia 'is_active' che il vecchio 'published'
        $isActive = $r->boolean('is_active') || $r->boolean('published');
        $featured = $r->boolean('featured');

        $data = $r->validate([
            'layout'   => 'required|in:text,image_left,image_right,hero',
            'title'    => 'nullable|string|max:255',
            'body'     => 'nullable|string',
            'position' => 'nullable|integer|min:1',
            // file validato separatamente per non rompere quando è assente:
            'image'    => 'nullable|image|max:4096',
        ]);

        $data['is_active'] = $isActive ? 1 : 0;
        $data['featured']  = $featured ? 1 : 0;

        // position di default
        if (empty($data['position'])) {
            $data['position'] = (AboutSection::max('position') ?? 0) + 1;
        }

        return $data;
    }
}