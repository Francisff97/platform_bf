<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BuilderController extends Controller
{
    public function index()
    {
        $builders = Builder::latest()->paginate(20);
        return view('admin.builders.index', compact('builders'));
    }

    public function create()
    {
        return view('admin.builders.create');
    }

    public function store(Request $r)
    {
        $data = $r->validate([
            'name'        => 'required|max:180',
            'slug'        => 'nullable|alpha_dash|unique:builders,slug',
            'team'        => 'nullable|max:180',
            'image'       => 'nullable|file|mimes:jpg,jpeg,png,webp|max:8192',
            'skills'      => 'nullable',
            'description' => 'nullable|string',
        ]);

        $data['slug'] = $data['slug'] ?? Str::slug($data['name']).'-'.Str::random(5);

        // normalizza skills (stringa o array)
        $skills = [];
        if (is_array($r->skills)) {
            $skills = array_values(array_filter(array_map('trim', $r->skills)));
        } elseif (is_string($r->skills)) {
            $skills = array_values(array_filter(array_map('trim', preg_split('/[,;\n]+/', $r->skills))));
        }
        $data['skills'] = $skills;

        if ($r->hasFile('image')) {
            $data['image_path'] = $r->file('image')->store('builders', 'public');
        }

        Builder::create($data);

        return redirect()->route('admin.builders.index')->with('success','Builder creato.');
    }

    public function edit(Builder $builder)
    {
        return view('admin.builders.edit', compact('builder'));
    }

    public function update(Request $r, Builder $builder)
    {
        $data = $r->validate([
            'name'        => 'required|max:180',
            'slug'        => "required|alpha_dash|unique:builders,slug,{$builder->id}",
            'team'        => 'nullable|max:180',
            'image'       => 'nullable|file|mimes:jpg,jpeg,png,webp|max:8192',
            'skills'      => 'nullable',
            'description' => 'nullable|string',
        ]);

        $skills = [];
        if (is_array($r->skills)) {
            $skills = array_values(array_filter(array_map('trim', $r->skills)));
        } elseif (is_string($r->skills)) {
            $skills = array_values(array_filter(array_map('trim', preg_split('/[,;\n]+/', $r->skills))));
        }
        $data['skills'] = $skills;

        if ($r->hasFile('image')) {
            $data['image_path'] = $r->file('image')->store('builders', 'public');
        }

        $builder->update($data);

        return back()->with('success','Builder aggiornato.');
    }

    public function destroy(Builder $builder)
    {
        $builder->delete();
        return back()->with('success','Builder eliminato.');
    }
}
