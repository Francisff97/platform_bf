// app/Http/Controllers/PwaController.php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PwaController extends Controller
{
    public function manifest()
    {
        $s = \App\Models\SiteSetting::first();

        $name        = $s->app_name         ?? config('app.name', 'Web App');
        $shortName   = $s->app_short_name   ?? $name;
        $theme       = $s->color_accent     ?? '#4f46e5';
        $bgLight     = $s->color_light_bg   ?? '#f8fafc';
        $startUrl    = url('/'); // home
        $faviconSvg  = url('/favicon.svg'); // logo base richiesto
        $faviconPng  = url('/favicon.png'); // fallback iOS/older (metti un 512x512 se puoi)
        $scope       = url('/');

        $manifest = [
            'name' => $name,
            'short_name' => $shortName,
            'start_url' => $startUrl,
            'scope' => $scope,
            'display' => 'standalone',
            'theme_color' => $theme,
            'background_color' => $bgLight,
            'icons' => [
                // Usiamo la favicon come “logo” dell’app
                [
                    'src' => $faviconSvg,
                    'sizes' => 'any',
                    'type' => 'image/svg+xml',
                    'purpose' => 'any maskable'
                ],
                // Fallback PNG per Android/iOS
                [ 'src' => $faviconPng, 'sizes' => '192x192', 'type' => 'image/png', 'purpose' => 'any' ],
                [ 'src' => $faviconPng, 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'any' ],
            ],
        ];

        return response()->json($manifest)
            ->header('Content-Type','application/manifest+json');
    }

    public function serviceWorker()
    {
        // SW minimale: cache-first per assets statici e “stale-while-revalidate” per immagini
        $sw = <<<JS
        const CACHE = 'app-cache-v1';

        self.addEventListener('install', (e) => {
          self.skipWaiting();
          e.waitUntil(caches.open(CACHE));
        });

        self.addEventListener('activate', (e) => {
          e.waitUntil(
            caches.keys().then(keys =>
              Promise.all(keys.filter(k => k !== CACHE).map(k => caches.delete(k)))
            )
          );
        });

        self.addEventListener('fetch', (e) => {
          const req = e.request;
          const url = new URL(req.url);

          // ignora richieste non GET
          if (req.method !== 'GET') return;

          // Cache first per css/js/fonts
          if (/\.(?:css|js|woff2?|ttf|eot)$/.test(url.pathname)) {
            e.respondWith(
              caches.match(req).then(r => r || fetch(req).then(res => {
                const clone = res.clone();
                caches.open(CACHE).then(c => c.put(req, clone));
                return res;
              }))
            );
            return;
          }

          // Stale-while-revalidate (immagini, compresi /cdn-cgi/image/)
          if (/\.(?:png|jpe?g|webp|gif|svg)$/.test(url.pathname) || url.pathname.includes('/cdn-cgi/image/')) {
            e.respondWith((async () => {
              const cache = await caches.open(CACHE);
              const cached = await cache.match(req);
              const fetchPromise = fetch(req).then(res => {
                cache.put(req, res.clone());
                return res;
              }).catch(() => cached);
              return cached || fetchPromise;
            })());
            return;
          }

          // default: network-first
          e.respondWith(
            fetch(req).catch(() => caches.match(req))
          );
        });
        JS;

        return response($sw, 200)->header('Content-Type','application/javascript');
    }
}