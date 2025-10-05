<?php

namespace App\Http\Controllers;

use App\Models\Pack;
use App\Models\Category;
use App\Models\Builder;
use Illuminate\Http\Request;

class PublicPackController extends Controller
{
    public function index(Request $request)
    {
        $q        = $request->string('q')->toString();
        $cat      = $request->string('category')->toString();
        $builder  = $request->string('builder')->toString();
        $sort     = $request->string('sort')->toString(); // latest|price_asc|price_desc

        $packs = Pack::with(['category','builder'])
            ->published()
            ->when($q, function($query) use ($q){
                $query->where(function($qq) use ($q) {
                    $qq->where('title','like',"%{$q}%")
                       ->orWhere('excerpt','like',"%{$q}%")
                       ->orWhere('description','like',"%{$q}%");
                });
            })
            ->when($cat, function($query) use ($cat){
                $query->whereHas('category', fn($cq) => $cq->where('slug', $cat));
            })
            ->when($builder, function($query) use ($builder){
                // builder può arrivare come id o slug
                $query->whereHas('builder', function($bq) use ($builder){
                    $bq->where('slug', $builder)->orWhere('id', $builder);
                });
            });

        // ordinamento
        $packs = match ($sort) {
            'price_asc'  => $packs->orderBy('price_cents','asc'),
            'price_desc' => $packs->orderBy('price_cents','desc'),
            default      => $packs->latest()
        };

        $packs = $packs->paginate(12)->withQueryString();

        $categories = Category::orderBy('name')->get(['id','name','slug','color']);
        $builders   = Builder::orderBy('name')->get(['id','name','slug']);

        return view('public.packs.index', compact('packs','categories','builders','q','cat','builder','sort'));
    }
}
