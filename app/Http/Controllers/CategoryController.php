<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    // Palette “sicura” (nomi Tailwind) + supporto HEX
    private array $allowed = ['indigo','emerald','rose','amber','sky','slate','violet','cyan','pink','lime','teal'];

    public function index() {
        $categories = Category::latest()->paginate(20);
        return view('admin.categories.index', compact('categories'));
    }

    public function create() {
        $palette = $this->allowed;
        return view('admin.categories.create', compact('palette'));
    }

    public function store(Request $r) {
        $data = $r->validate([
            'name'  => 'required|max:120',
            'slug'  => 'nullable|alpha_dash|unique:categories,slug',
            'color' => ['nullable','max:32','regex:/^#[0-9A-Fa-f]{6}$|^[a-z\-]+$/'], // HEX o nome
        ]);
        $data['slug'] = $data['slug'] ?: Str::slug($data['name']);

        // normalizza: se non in palette e non HEX, fallback
        if ($data['color'] && !in_array($data['color'], $this->allowed) && !preg_match('/^#[0-9A-Fa-f]{6}$/', $data['color'])) {
            $data['color'] = 'indigo';
        }
        Category::create($data);
        return redirect()->route('admin.categories.index')->with('success','Categoria creata.');
    }

    public function edit(Category $category) {
        $palette = $this->allowed;
        return view('admin.categories.edit', compact('category','palette'));
    }

    public function update(Request $r, Category $category) {
        $data = $r->validate([
            'name'  => 'required|max:120',
            'slug'  => "required|alpha_dash|unique:categories,slug,{$category->id}",
            'color' => ['nullable','max:32','regex:/^#[0-9A-Fa-f]{6}$|^[a-z\-]+$/'],
        ]);
        if ($data['color'] && !in_array($data['color'], $this->allowed) && !preg_match('/^#[0-9A-Fa-f]{6}$/', $data['color'])) {
            $data['color'] = 'indigo';
        }
        $category->update($data);
        return back()->with('success','Categoria aggiornata.');
    }

    public function destroy(Category $category) {
        $category->delete();
        return back()->with('success','Categoria eliminata.');
    }
}