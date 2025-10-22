const CACHE = 'bf-v1';
const ASSETS = [
  '/',                 // homepage
  '/manifest.webmanifest',
  '/favicon-192.png',
  '/favicon.png'
];

// install
self.addEventListener('install', (event) => {
  event.waitUntil(caches.open(CACHE).then(c => c.addAll(ASSETS)));
  self.skipWaiting();
});

// activate
self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then(keys =>
      Promise.all(keys.filter(k => k !== CACHE).map(k => caches.delete(k)))
    )
  );
  self.clients.claim();
});

// fetch (network first con fallback cache)
self.addEventListener('fetch', (event) => {
  const req = event.request;
  if (req.method !== 'GET') return; // non interferire con POST ecc.
  event.respondWith(
    fetch(req).then(res => {
      const copy = res.clone();
      caches.open(CACHE).then(c => c.put(req, copy)).catch(()=>{});
      return res;
    }).catch(() => caches.match(req))
  );
});
