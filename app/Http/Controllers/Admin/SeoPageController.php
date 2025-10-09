<?php

namespace App\Http\Controllers\Admin;

   // app/Http/Controllers/Admin/SeoPageController.php
use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\Controller;
use App\Models\SeoPage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SeoPageController extends Controller
{
    public function index()
    {
        $pages = SeoPage::orderBy('route_name')->paginate(20);
        return view('admin.seo.pages.index', compact('pages'));
    }

    public function create()
    {
        $publicRoutes = collect(app('router')->getRoutes())
            ->filter(fn($r)=> in_array('GET',$r->methods()))
            ->map(fn($r)=> $r->getName())
            ->filter()
            ->unique()
            ->sort()
            ->values();

        return view('admin.seo.pages.create', compact('publicRoutes'));
    }

    public function store(Request $r)
    {
        $data = $r->validate([
            'route_name' => ['nullable','string','max:255'],
            'path'       => ['nullable','string','max:255'],
            'meta_title' => ['nullable','string','max:255'],
            'meta_description' => ['nullable','string'],
            'og_image'   => ['nullable','image','mimes:jpg,jpeg,png,webp,avif','max:4096'],
        ]);

        $page = new SeoPage($data);
        if ($r->hasFile('og_image')) {
            $page->og_image_path = $r->file('og_image')->store('seo/og','public');
        }
        $page->save();

        return redirect()->route('admin.seo.pages.index')->with('success','SEO page saved.');
    }

    public function edit(SeoPage $seoPage)
    {
        $publicRoutes = collect(app('router')->getRoutes())
            ->filter(fn($r)=> in_array('GET',$r->methods()))
            ->map(fn($r)=> $r->getName())
            ->filter()
            ->unique()
            ->sort()
            ->values();

        return view('admin.seo.pages.edit', compact('seoPage','publicRoutes'));
    }

    public function update(Request $r, SeoPage $seoPage)
    {
        $data = $r->validate([
            'route_name' => ['nullable','string','max:255'],
            'path'       => ['nullable','string','max:255'],
            'meta_title' => ['nullable','string','max:255'],
            'meta_description' => ['nullable','string'],
            'og_image'   => ['nullable','image','mimes:jpg,jpeg,png,webp,avif','max:4096'],
        ]);

        $seoPage->fill($data);
        if ($r->hasFile('og_image')) {
            $seoPage->og_image_path = $r->file('og_image')->store('seo/og','public');
        }
        $seoPage->save();

        return back()->with('success','SEO page updated.');
    }

    public function destroy(SeoPage $seoPage)
    {
        $seoPage->delete();
        return back()->with('success','Deleted.');
    }
 

public function sync()
{
    Artisan::call('seo:pages-backfill');

    return back()->with('success', 'Pages synchronized successfully!');
}
}
