<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route as RouteFacade;

class SitemapController extends Controller
{
    public function index(Request $request)
    {
        $xml = Cache::remember('sitemap.xml', now()->addMinutes(30), function () {
            $urls = [];

            // --- STATICHE sicure (metti quelle che hai davvero) ---
            $pushUrl = function (string $url, string $prio = '0.6', ?string $lastmod = null) use (&$urls) {
                $urls[] = ['loc' => $url, 'prio' => $prio, 'lastmod' => $lastmod];
            };

            // Home + altre pagine note (se esistono)
            foreach ([
                'home'           => '1.0',
                'packs.public'   => '0.9',
                'builders.index' => '0.7',
                'services.public'=> '0.7',
                'coaches.index'  => '0.6',
                'contacts'       => '0.3',
            ] as $name => $prio) {
                if (\Route::has($name)) {
                    $pushUrl(route($name), $prio);
                }
            }

            // --- DINAMICHE (Pack / Builder / Coach / Service) ---
            // NB: ogni blocco è "opzionale": eseguito solo se il modello/rotta esistono.
            if (class_exists(\App\Models\Pack::class) && \Route::has('packs.show')) {
                foreach (\App\Models\Pack::where('status','published')->cursor() as $p) {
                    $pushUrl(
                        route('packs.show', $p->slug),
                        '0.8',
                        optional($p->updated_at)->toAtomString()
                    );
                }
            }

            if (class_exists(\App\Models\Builder::class) && \Route::has('builders.show')) {
                foreach (\App\Models\Builder::cursor() as $b) {
                    $pushUrl(
                        route('builders.show', $b->slug),
                        '0.6',
                        optional($b->updated_at)->toAtomString()
                    );
                }
            }

            if (class_exists(\App\Models\Coach::class) && \Route::has('coaches.show')) {
                foreach (\App\Models\Coach::cursor() as $c) {
                    $pushUrl(
                        route('coaches.show', $c->slug),
                        '0.6',
                        optional($c->updated_at)->toAtomString()
                    );
                }
            }

            if (class_exists(\App\Models\Service::class) && \Route::has('services.show')) {
                foreach (\App\Models\Service::cursor() as $s) {
                    $pushUrl(
                        route('services.show', $s->slug ?? $s->id),
                        '0.5',
                        optional($s->updated_at)->toAtomString()
                    );
                }
            }

            // --- AUTODISCOVERY PUBLIC ROUTES (GET, senza auth/admin/verified, senza parametri obbligatori) ---
            try {
                /** @var \Illuminate\Routing\Route $r */
                foreach (RouteFacade::getRoutes() as $r) {
                    if (!in_array('GET', $r->methods(), true)) continue;
                    $uri = $r->uri();                               // es. "about" o "packs/{slug}"
                    if (str_contains($uri, '{') && !str_contains($uri, '?}')) continue; // param obbligatorio -> skip

                    $mw = collect($r->gatherMiddleware())->implode(',');
                    if (preg_match('/\b(auth|admin|verified)\b/i', $mw)) continue;      // route non public

                    // Costruisci URL assoluto solo per path “semplici”
                    if (!str_contains($uri, '{')) {
                        $url = url($uri === '/' ? '' : $uri);
                        // Evita duplicati con quelli già aggiunti
                        if (!collect($urls)->contains(fn($u) => $u['loc'] === $url)) {
                            $pushUrl($url, '0.4');
                        }
                    }
                }
            } catch (\Throwable $e) {
                // Silenzioso: l’autodiscovery è “best effort”
            }

            // --- Render XML a mano (niente Blade) ---
            $escape = fn($s) => htmlspecialchars($s, ENT_XML1 | ENT_COMPAT, 'UTF-8');
            $body = "<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n";
            foreach ($urls as $u) {
                $body .= "  <url>\n";
                $body .= "    <loc>{$escape($u['loc'])}</loc>\n";
                if (!empty($u['lastmod'])) $body .= "    <lastmod>{$escape($u['lastmod'])}</lastmod>\n";
                if (!empty($u['prio']))    $body .= "    <priority>{$escape($u['prio'])}</priority>\n";
                $body .= "  </url>\n";
            }
            $body .= "</urlset>\n";

            return '<?xml version="1.0" encoding="UTF-8"?>' . "\n" . $body;
        });

        return response($xml, 200, [
            'Content-Type'  => 'application/xml; charset=UTF-8',
            'Cache-Control' => 'public, max-age=1800',
        ]);
    }
}