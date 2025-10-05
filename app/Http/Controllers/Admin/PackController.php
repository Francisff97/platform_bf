<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pack;
use App\Models\Category;
use App\Models\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PackController extends Controller
{
    public function index()
    {
        $packs = Pack::latest()->paginate(20);
        return view('admin.packs.index', compact('packs'));
    }

    public function create()
    {
        $categories = Category::orderBy('name')->get();
        $builders   = Builder::orderBy('name')->get();
        return view('admin.packs.create', compact('categories','builders'));
    }

    public function store(Request $r)
    {
        $data = $r->validate([
            'title'        => 'required|max:180',
            'slug'         => 'nullable|alpha_dash|unique:packs,slug',
            'excerpt'      => 'nullable|max:255',
            'description'  => 'nullable',
            'image'        => 'nullable|file|mimes:jpg,jpeg,png,webp|max:8192', // 8MB
            'price_cents'  => 'required|integer|min:0',
            'currency'     => 'required|string|size:3',
            'is_featured'  => 'sometimes|boolean',
            'builder_id'   => 'nullable|exists:builders,id',
            'status'       => 'required|in:draft,published',
            'published_at' => 'nullable|date',
            'category_id'  => 'nullable|exists:categories,id',
        ]);

        $data['slug'] = $data['slug'] ?? Str::slug($data['title']).'-'.Str::random(5);
        $data['is_featured'] = !empty($data['is_featured']);

        if ($r->hasFile('image')) {
            $data['image_path'] = $r->file('image')->store('packs', 'public');
        }

        Pack::create($data);

        return redirect()->route('admin.packs.index')->with('success','Pack creato.');
    }

    public function edit(Pack $pack)
    {
        $categories = Category::orderBy('name')->get();
        $builders   = Builder::orderBy('name')->get();
        return view('admin.packs.edit', compact('pack','categories','builders'));
    }

    public function update(Request $r, Pack $pack)
    {
        $data = $r->validate([
            'title'        => 'required|max:180',
            'slug'         => "required|alpha_dash|unique:packs,slug,{$pack->id}",
            'excerpt'      => 'nullable|max:255',
            'description'  => 'nullable',
            'image'        => 'nullable|file|mimes:jpg,jpeg,png,webp|max:8192', // 8MB
            'price_cents'  => 'required|integer|min:0',
            'currency'     => 'required|string|size:3',
            'is_featured'  => 'sometimes|boolean',
            'builder_id'   => 'nullable|exists:builders,id',
            'status'       => 'required|in:draft,published',
            'published_at' => 'nullable|date',
            'category_id'  => 'nullable|exists:categories,id',
        ]);

        $data['is_featured'] = !empty($data['is_featured']);

        if ($r->hasFile('image')) {
            $data['image_path'] = $r->file('image')->store('packs', 'public');
        }

        $pack->update($data);

        return back()->with('success','Pack aggiornato.');
    }

    public function destroy(Pack $pack)
    {
        $pack->delete();
        return back()->with('success','Pack eliminato.');
    }
}
