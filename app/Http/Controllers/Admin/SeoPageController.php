<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SeoPage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

class SeoPageController extends Controller
{
    // -------------------------
    // INDEX
    // -------------------------
    public function index()
    {
        $pages = SeoPage::orderByRaw('COALESCE(route_name, path) asc')->paginate(20);
        return view('admin.seo.pages.index', compact('pages'));
    }

    // -------------------------
    // CREATE
    // -------------------------
    public function create()
    {
        $publicRoutes = $this->publicNamedGetRoutes();
        return view('admin.seo.pages.create', compact('publicRoutes'));
    }

    // -------------------------
    // STORE
    // -------------------------
    public function store(Request $r)
    {
        $data = $r->validate([
            'route_name'       => ['nullable','string','max:255'],
            'path'             => ['nullable','string','max:255'],
            'meta_title'       => ['nullable','string','max:255'],
            'meta_description' => ['nullable','string'],
            // accettiamo SIA un file OG SIA un path testuale
            'og_image'         => ['nullable','image','mimes:jpg,jpeg,png,webp,avif','max:4096'],
            'og_image_path'    => ['nullable','string','max:2048'],
        ]);

        $page = new SeoPage($data);

        // se carichi un file, ha priorità e sovrascrive og_image_path
        if ($r->hasFile('og_image')) {
            $page->og_image_path = $r->file('og_image')->store('seo/og', 'public');
        }

        $page->save();

        return redirect()
            ->route('admin.seo.pages.index')
            ->with('success','SEO page saved.');
    }

    // -------------------------
    // EDIT
    // -------------------------
    public function edit(SeoPage $seoPage)
    {
        $publicRoutes   = $this->publicNamedGetRoutes();
        $exampleContext = $this->buildExampleContext($seoPage->route_name);

        return view('admin.seo.pages.edit', compact('seoPage','publicRoutes','exampleContext'));
    }

    // -------------------------
    // UPDATE
    // -------------------------
    public function update(Request $r, SeoPage $seoPage)
    {
        $data = $r->validate([
            'route_name'       => ['nullable','string','max:255'],
            'path'             => ['nullable','string','max:255'],
            'meta_title'       => ['nullable','string','max:255'],
            'meta_description' => ['nullable','string'],
            'og_image'         => ['nullable','image','mimes:jpg,jpeg,png,webp,avif','max:4096'],
            'og_image_path'    => ['nullable','string','max:2048'],
        ]);

        $seoPage->fill($data);

        if ($r->hasFile('og_image')) {
            // caricato nuovo file → priorità al file
            $seoPage->og_image_path = $r->file('og_image')->store('seo/og', 'public');
        }
        // se NON carichi file, resta quanto già in $data['og_image_path'] (anche con variabili)

        $seoPage->save();

        return back()->with('success','SEO page updated.');
    }

    // -------------------------
    // DESTROY
    // -------------------------
    public function destroy(SeoPage $seoPage)
    {
        $seoPage->delete();
        return back()->with('success','Deleted.');
    }

    // -------------------------
    // SYNC (backfill)
    // -------------------------
    public function sync()
    {
        Artisan::call('seo:pages-backfill');
        return back()->with('success', 'Pages synchronized successfully!');
    }

    // =====================================================
    // Helpers
    // =====================================================

    /** Ritorna l’elenco delle route GET nominale (name() non nullo) ordinate. */
    protected function publicNamedGetRoutes()
    {
        return collect(app('router')->getRoutes())
            ->filter(fn($r) => in_array('GET', $r->methods()))
            ->map(fn($r) => $r->getName())
            ->filter()     // rimuove null
            ->unique()
            ->sort()
            ->values();
    }

    /**
     * Costruisce un contesto di esempio per la live preview,
     * in base alla route .show (packs.show, builders.show, coaches.show, services.show).
     * Estendi facilmente aggiungendo altri case.
     */
    protected function buildExampleContext(?string $routeName): array
    {
        $base = [
            'name'         => 'Sample name',
            'slug'         => 'sample-slug',
            'excerpt'      => 'Short teaser about the item.',
            'description'  => 'Longer description of the current item shown on this page.',
            'image_url'    => 'https://via.placeholder.com/1200x630.png?text=OG+Preview',
            'price_eur'    => '19,99 EUR',
            'builder_name' => 'Sample Builder',
        ];

        if (!$routeName) {
            return $base;
        }

        // Packs
        if ($routeName === 'packs.show' || str_ends_with($routeName, 'packs.show')) {
            if (class_exists(\App\Models\Pack::class)) {
                $m = \App\Models\Pack::query()->latest('id')->first();
                if ($m) {
                    $base['name']         = (string)($m->title ?? $base['name']);
                    $base['slug']         = (string)($m->slug  ?? $base['slug']);
                    $base['excerpt']      = (string)($m->excerpt ?? $base['excerpt']);
                    $base['description']  = (string)($m->description ?? $base['description']);
                    $base['image_url']    = $m->image_path ? Storage::disk('public')->url($m->image_path) : $base['image_url'];
                    $base['price_eur']    = isset($m->price_cents)
                        ? number_format($m->price_cents/100, 2, ',', '.') . ' ' . ($m->currency ?? 'EUR')
                        : $base['price_eur'];
                    $base['builder_name'] = optional($m->builder)->name ?? $base['builder_name'];
                }
            }
            return $base;
        }

        // Builders
        if ($routeName === 'builders.show' || str_ends_with($routeName, 'builders.show')) {
            if (class_exists(\App\Models\Builder::class)) {
                $m = \App\Models\Builder::query()->latest('id')->first();
                if ($m) {
                    $base['name']        = (string)($m->name ?? $base['name']);
                    $base['slug']        = (string)($m->slug ?? $base['slug']);
                    $base['description'] = (string)($m->description ?? $base['description']);
                    $base['image_url']   = $m->image_path ? Storage::disk('public')->url($m->image_path) : $base['image_url'];
                }
            }
            return $base;
        }

        // Coaches
        if ($routeName === 'coaches.show' || str_ends_with($routeName, 'coaches.show')) {
            if (class_exists(\App\Models\Coach::class)) {
                $m = \App\Models\Coach::query()->latest('id')->first();
                if ($m) {
                    $base['name']       = (string)($m->name ?? $base['name']);
                    $base['slug']       = (string)($m->slug ?? $base['slug']);
                    $base['image_url']  = $m->image_path ? Storage::disk('public')->url($m->image_path) : $base['image_url'];
                    $base['description']= 'One of our best coaches. ' . ($m->team ? "Team: {$m->team}." : '');
                }
            }
            return $base;
        }

        // Services
        if ($routeName === 'services.show' || str_ends_with($routeName, 'services.show')) {
            if (class_exists(\App\Models\Service::class)) {
                $m = \App\Models\Service::query()->latest('id')->first();
                if ($m) {
                    $base['name']        = (string)($m->name ?? $base['name']);
                    $base['slug']        = (string)($m->slug ?? $base['slug']);
                    $base['excerpt']     = (string)($m->excerpt ?? $base['excerpt']);
                    $base['description'] = (string)($m->body ?? $base['description']);
                    $base['image_url']   = $m->image_path ? Storage::disk('public')->url($m->image_path) : $base['image_url'];
                }
            }
            return $base;
        }

        // Default
        return $base;
    }
}
