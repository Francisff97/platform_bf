<?php

namespace App\Http\Controllers;

use App\Models\Pack;
use App\Models\Service;
use App\Models\Builder;
use App\Models\Slide;
use App\Models\AboutSection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Http\Request;

// 👇 SEO
use App\Support\SeoManager;

class PageController extends Controller
{
    // HOME
    public function home()
    {
        $slides = Slide::query()
            ->when(
                Schema::hasColumn('slides', 'is_active'),
                fn ($q) => $q->where('is_active', true)
            )
            ->orderBy('sort_order')
            ->get();

        $latestPack = Pack::published()->latest()->first();
        $otherPacks = Pack::published()->latest()->skip(1)->take(4)->get();
        $builders   = Builder::latest()->get();
        $coaches    = class_exists(\App\Models\Coach::class)
            ? \App\Models\Coach::latest()->take(6)->get()
            : collect();
        $services   = Service::latest()->get();

        // contesto opzionale per variabili generiche
        $seoCtx = [
            'name'        => config('app.name'),
            'title'       => 'Home',
            'slug'        => 'home',
            'description' => null,
        ];

        return view('public.home', compact(
            'slides', 'latestPack', 'otherPacks', 'builders', 'coaches', 'services', 'seoCtx'
        ));
    }

    // ABOUT
    public function about()
    {
        $sections = AboutSection::query()
            ->where('is_active', true)
            ->orderBy('position')
            ->orderBy('id')
            ->get();

        $seoCtx = [
            'title' => 'About us',
            'slug'  => 'about-us',
        ];

        return view('public.about', compact('sections','seoCtx'));
    }

    // SERVICES (index)
    public function services()
    {
        $services = Service::published()->orderBy('order')->get();

        $seoCtx = [
            'title' => 'Services',
            'slug'  => 'services',
        ];

        return view('public.services.index', compact('services','seoCtx'));
    }

    // PACKS (index)
    public function packs()
    {
        $packs = Pack::published()->latest('published_at')->paginate(12);

        $seoCtx = [
            'title' => 'Packs',
            'slug'  => 'packs',
        ];

        return view('public.packs.index', compact('packs','seoCtx'));
    }

    // PACK (show) —— QUI usiamo il modello per costruire il contesto SEO
    public function packShow($slug)
    {
        $pack = Pack::published()->where('slug',$slug)->firstOrFail();

        // crea contesto con tutte le chiavi utili ({name},{title},{slug},{price_eur},{image_url}, ecc.)
        $seoCtx = SeoManager::contextFromModel($pack);

        return view('public.packs.show', compact('pack','seoCtx'));
    }

    // CONTACT
    public function contact()
    {
        $seoCtx = [
            'title' => 'Contact',
            'slug'  => 'contacts',
        ];

        return view('public.contact', compact('seoCtx'));
    }
}
