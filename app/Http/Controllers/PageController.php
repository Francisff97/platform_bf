<?php

namespace App\Http\Controllers;

use App\Models\Pack;
use App\Models\Service;
use App\Models\Builder;
use App\Models\Slide;
use App\Models\AboutSection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Http\Request;


class PageController extends Controller
{
    // app/Http/Controllers/PageController.php

    public function home()
    {
        $slides = Slide::query()
            ->when(
                Schema::hasColumn('slides', 'is_active'), // 👈 USA LA FACADE
                fn ($q) => $q->where('is_active', true)
            )
            ->orderBy('sort_order')
            ->get();

        $latestPack = Pack::published()->latest()->first();
        $otherPacks   = Pack::published()->latest()->skip(1)->take(4)->get();

        $builders = Builder::latest()->get();

        $coaches = class_exists(\App\Models\Coach::class)
            ? \App\Models\Coach::latest()->take(6)->get()
            : collect();

        $services = Service::latest()->get();

        return view('public.home', compact(
            'slides', 'latestPack', 'otherPacks', 'builders', 'coaches', 'services'
        ));
    }


    public function about()
    {
        // Prende solo le sezioni attive, ordinate
        $sections = AboutSection::query()
            ->where('is_active', true)
            ->orderBy('position')
            ->orderBy('id')
            ->get();

        return view('public.about', compact('sections'));
    }

    public function services()
    {
        $services = Service::published()->orderBy('order')->get();
        return view('public.services.index', compact('services'));
    }

    public function packs()
    {
        $packs = Pack::published()->latest('published_at')->paginate(12);
        return view('public.packs.index', compact('packs'));
    }

    public function packShow($slug)
    {
        $pack = Pack::published()->where('slug',$slug)->firstOrFail();
        return view('public.packs.show', compact('pack'));
    }

    public function contact()
    {
        return view('public.contact');
    }
}
