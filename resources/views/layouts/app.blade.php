<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <!-- ===============================
       🌐 BASE META
  =============================== -->
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">

  <link rel="icon" href="/favicon.ico">
  <link rel="icon" type="image/svg+xml" href="/favicon.svg">

  <!-- HSTS / CSP / COEP / COOP: gestiti da NGINX (non qui) -->

  <!-- ===============================
       🔍 SEO (SeoManager)
  =============================== -->
  @php
    use App\Support\SeoManager;
    $meta = SeoManager::pageMeta(null, null, $seoCtx ?? []);
  @endphp
  <title>{{ $meta['title'] ?? config('app.name') }}</title>
  @if(!empty($meta['description'])) <meta name="description" content="{{ $meta['description'] }}"> @endif
  <meta property="og:title" content="{{ $meta['title'] ?? config('app.name') }}">
  <meta property="og:description" content="{{ $meta['description'] ?? '' }}">
  @if(!empty($meta['og_image'])) <meta property="og:image" content="{{ $meta['og_image'] }}"> @endif
  <meta property="og:type" content="website">
  <meta name="twitter:card" content="summary_large_image">

  <!-- ===============================
       ⚡ Preconnect essenziali (<=4)
  =============================== -->
  <link rel="dns-prefetch" href="//fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

  <link rel="dns-prefetch" href="//unpkg.com">
  <link rel="preconnect" href="https://unpkg.com" crossorigin>

  <!-- Thumbnails YouTube per cover "Play" -->
  <link rel="dns-prefetch" href="//i.ytimg.com">
  <link rel="preconnect" href="https://i.ytimg.com" crossorigin>

  <!-- ===============================
       🧩 Google Tag Manager (se presente)
  =============================== -->
  @php $gtm = optional(\App\Models\SiteSetting::first())->gtm_container_id; @endphp
  @if($gtm)
    <script>
      /* dataLayer prima del tag GTM */
      window.dataLayer = window.dataLayer || [];
      function trackEvent(eventName, data = {}) { window.dataLayer.push({ event: eventName, ...data }); }
    </script>
    <script>
      (function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':new Date().getTime(),event:'gtm.js'});
        var f=d.getElementsByTagName(s)[0], j=d.createElement(s), dl=l!='dataLayer'?'&l='+l:'';
        j.async=true; j.src='https://www.googletagmanager.com/gtm.js?id='+i+dl; f.parentNode.insertBefore(j,f);
      })(window,document,'script','dataLayer','{{ $gtm }}');
    </script>
  @endif

  <!-- ===============================
       🖋️ Google Fonts + Swiper CSS
  =============================== -->
  <link rel="preload"
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap"
        as="style" onload="this.onload=null;this.rel='stylesheet'">
  <noscript><link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap"></noscript>

  <link rel="preload"
        href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;600;700&display=swap"
        as="style" onload="this.onload=null;this.rel='stylesheet'">
  <noscript><link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;600;700&display=swap"></noscript>

  <link rel="preload" href="https://unpkg.com/swiper@10.3.1/swiper-bundle.min.css"
        as="style" onload="this.onload=null;this.rel='stylesheet'">
  <noscript><link rel="stylesheet" href="https://unpkg.com/swiper@10.3.1/swiper-bundle.min.css"></noscript>

  <!-- Swiper JS non-bloccante -->
  <script defer src="https://unpkg.com/swiper@10.3.1/swiper-bundle.min.js"></script>

  <!-- ===============================
       🎨 Theme bootstrap (inline)
       *Se usi CSP strict, ricorda di aggiungere nonce/hash lato server*
  =============================== -->
  <script>
    (function(){
      try{
        var d=document.documentElement;
        var cookieTheme=(document.cookie.match(/(?:^|;\s*)theme=([^;]+)/)||[,''])[1].toLowerCase();
        var lsTheme=(localStorage.getItem('theme')||'').toLowerCase();
        var mode=cookieTheme||lsTheme||'system';
        if(!['light','dark','system'].includes(mode)) mode='system';
        var prefersDark=false; try{prefersDark=matchMedia('(prefers-color-scheme: dark)').matches}catch(e){}
        d.classList.toggle('dark', mode==='dark'||(mode==='system'&&prefersDark));
        window.setTheme=function(next){
          next=(next||'system').toLowerCase();
          if(!['light','dark','system'].includes(next)) next='system';
          localStorage.setItem('theme',next);
          document.cookie='theme='+next+'; Path=/; Max-Age=31536000; SameSite=Lax';
          var p=false; try{p=matchMedia('(prefers-color-scheme: dark)').matches}catch(e){}
          d.classList.toggle('dark', next==='dark'||(next==='system'&&p));
        };
        window.toggleTheme=function(){
          var cur=(localStorage.getItem('theme')||'system').toLowerCase();
          setTheme(cur==='dark'?'light':'dark');
        };
      }catch(e){}
    })();
  </script>

  <!-- ===============================
       🎨 Colori globali (inline critico)
  =============================== -->
  @php $s = \App\Models\SiteSetting::first(); @endphp
  <style>
    :root{
      --bg-light: {{ $s->color_light_bg ?? '#f8fafc' }};
      --accent:   {{ $s->color_accent   ?? '#4f46e5' }};
    }
    .dark{ --bg-dark: {{ $s->color_dark_bg ?? '#0b0f1a' }}; }
    [x-cloak]{display:none!important}
  </style>

  <!-- ===============================
       📢 Iubenda banner (se attivo)
  =============================== -->
  @if(($privacySettings?->banner_enabled) && $privacySettings?->banner_head_code)
    {!! $privacySettings->banner_head_code !!}
  @endif

  <!-- ===============================
       🧩 Vite bundle
  =============================== -->
  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<style>[x-cloak]{display:none!important}</style>
  <body class="min-h-screen bg-[var(--bg-light)] dark:bg-[var(--bg-dark)] text-gray-900 dark:text-gray-100 font-sans">
    @if($gtm)
      <!-- Google Tag Manager (noscript) -->
      <noscript><iframe src="https://www.googletagmanager.com/ns.html?id={{ $gtm }}"
      height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
      <!-- End Google Tag Manager (noscript) -->
    @endif

    <div class="min-h-screen">
      {{-- Navbar moderna --}}
      <x-site-nav />

      {{-- Page Heading (opzionale) --}}
      @isset($header)
        <header class="bg-white shadow dark:bg-neutral-900">
          <div class="mx-auto max-w-7xl py-6 px-4 sm:px-6 lg:px-8">
            {{ $header }}
          </div>
        </header>
      @endisset

      {{-- Page Content --}}
      <main class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-8">
        @if (session('success'))
          <div class="mb-6 rounded border border-green-200 bg-green-50 px-4 py-3 text-green-800">
            {{ session('success') }}
          </div>
        @endif

        {{ $slot }}
      </main>

      <x-site-footer />
    </div>
  @if(($privacySettings?->banner_enabled) && $privacySettings?->banner_body_code)
  {!! $privacySettings->banner_body_code !!}

  <script>
    (function () {
      var loaded = false;
      function loadIubenda() {
        if (loaded) return;
        loaded = true;
        var s = document.createElement('script');
        s.src = 'https://cdn.iubenda.com/iubenda.js';
        s.async = true;
        (document.head || document.body).appendChild(s);

        // stacca i listener
        window.removeEventListener('scroll', loadIubenda, opts);
        window.removeEventListener('pointerdown', loadIubenda, opts);
        window.removeEventListener('keydown', loadIubenda, opts);
      }

      var opts = { once: true, passive: true };
      // primo evento utente: scroll / tocco / tasto
      window.addEventListener('scroll', loadIubenda, opts);
      window.addEventListener('pointerdown', loadIubenda, opts);
      window.addEventListener('keydown', loadIubenda, { once: true });

      // fallback: carica comunque dopo 2.5s
      setTimeout(loadIubenda, 4500);
    })();
  </script>
@endif
  </body>
</html>
