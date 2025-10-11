<?php
namespace App\Http\Controllers;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class SitemapController extends Controller
{
  public function index()
  {
    $xml = Cache::remember('sitemap.xml', now()->addMinutes(30), function(){
      $base = rtrim(config('app.url'),'/');

      $urls = [];

      // --- statiche principali
      $urls[] = ['loc'=>route('home'),               'prio'=>'1.0'];
      if (route::has('packs.public'))     $urls[] = ['loc'=>route('packs.public'),     'prio'=>'0.9'];
      if (route::has('builders.index'))   $urls[] = ['loc'=>route('builders.index'),   'prio'=>'0.7'];
      if (route::has('services.public'))  $urls[] = ['loc'=>route('services.public'),  'prio'=>'0.7'];
      if (route::has('coaches.index'))    $urls[] = ['loc'=>route('coaches.index'),    'prio'=>'0.6'];
      if (route::has('contacts'))         $urls[] = ['loc'=>route('contacts'),         'prio'=>'0.3'];

      // --- dinamiche
      foreach (\App\Models\Pack::query()->where('status','published')->cursor() as $p) {
        $urls[] = [
          'loc'=> route('packs.show',$p->slug),
          'lastmod'=> optional($p->updated_at)->toAtomString(),
          'prio'=> '0.8',
        ];
      }
      foreach (\App\Models\Builder::cursor() as $b) {
        $urls[] = [
          'loc'=> route('builders.show',$b->slug),
          'lastmod'=> optional($b->updated_at)->toAtomString(),
          'prio'=> '0.6',
        ];
      }
      foreach (\App\Models\Coach::cursor() as $c) {
        $urls[] = [
          'loc'=> route('coaches.show',$c->slug),
          'lastmod'=> optional($c->updated_at)->toAtomString(),
          'prio'=> '0.6',
        ];
      }
      foreach (\App\Models\Service::cursor() as $s) {
        $urls[] = [
          'loc'=> route('services.show',$s->slug ?? $s->id),
          'lastmod'=> optional($s->updated_at)->toAtomString(),
          'prio'=> '0.5',
        ];
      }

      // XML render
      $body = view('sitemap.xml', compact('urls'))->render();
      return "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n".$body;
    });

    return response($xml, 200, ['Content-Type'=>'application/xml; charset=UTF-8']);
  }
}
