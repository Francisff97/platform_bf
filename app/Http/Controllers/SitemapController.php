<?php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\Cache;

class SitemapController extends Controller
{
    public function index()
    {
        $xml = Cache::remember('sitemap.xml', now()->addMinutes(30), function () {
            $urls = [];

            // statiche
            $urls[] = ['loc'=>route('home'),'prio'=>'1.0'];
            foreach ([
                'packs.public'   => '0.9',
                'builders.index' => '0.7',
                'services.public'=> '0.7',
                'coaches.index'  => '0.6',
                'contacts'       => '0.3',
            ] as $r => $prio) {
                if (\Route::has($r)) $urls[] = ['loc'=>route($r),'prio'=>$prio];
            }

            // dinamiche
            foreach (\App\Models\Pack::where('status','published')->cursor() as $p) {
                $urls[] = ['loc'=>route('packs.show',$p->slug),'lastmod'=>optional($p->updated_at)->toAtomString(),'prio'=>'0.8'];
            }
            foreach (\App\Models\Builder::cursor() as $b) {
                $urls[] = ['loc'=>route('builders.show',$b->slug),'lastmod'=>optional($b->updated_at)->toAtomString(),'prio'=>'0.6'];
            }
            foreach (\App\Models\Coach::cursor() as $c) {
                $urls[] = ['loc'=>route('coaches.show',$c->slug),'lastmod'=>optional($c->updated_at)->toAtomString(),'prio'=>'0.6'];
            }
            if (\Route::has('services.show')) {
    foreach (\App\Models\Service::cursor() as $s) {
        $urls[] = [
            'loc' => route('services.show', $s->slug ?? $s->id),
            'lastmod' => optional($s->updated_at)->toAtomString(),
            'prio' => '0.5'
        ];
    }
}

            $body = view('sitemap.xml', compact('urls'))->render();
            return '<?xml version="1.0" encoding="UTF-8"?>'."\n".$body;
        });

        return response($xml, 200, ['Content-Type'=>'application/xml; charset=UTF-8']);
    }
}
