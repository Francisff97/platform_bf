<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  {{-- === Performance hints (keep at most ~4 preconnects) === --}}
  <link rel="dns-prefetch" href="//fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

  <link rel="dns-prefetch" href="//unpkg.com">
  <link rel="preconnect" href="https://unpkg.com" crossorigin>

  {{-- YouTube thumbnails (only if we show poster images) --}}
  <link rel="dns-prefetch" href="//i.ytimg.com">
  <link rel="preconnect" href="https://i.ytimg.com" crossorigin>

  {{-- Iubenda (only if present on page) --}}
  <link rel="dns-prefetch" href="//embeds.iubenda.com">
  <link rel="preconnect" href="https://embeds.iubenda.com" crossorigin>

  @php
    use App\Support\SeoManager;
    $meta = SeoManager::pageMeta(null, null, $seoCtx ?? []);
  @endphp

  <title>{{ $meta['title'] ?? config('app.name') }}</title>
  @if(!empty($meta['description']))
    <meta name="description" content="{{ $meta['description'] }}">
  @endif
  @if(!empty($meta['og_image']))
    <meta property="og:image" content="{{ $meta['og_image'] }}">
  @endif
  <meta property="og:title" content="{{ $meta['title'] ?? config('app.name') }}">
  <meta property="og:description" content="{{ $meta['description'] ?? '' }}">
  <meta property="og:type" content="website">
  <meta name="twitter:card" content="summary_large_image">

  {{-- Google Tag Manager (JS) --}}
  @php $gtm = optional(\App\Models\SiteSetting::first())->gtm_container_id; @endphp
  @if($gtm)
    <script>
      (function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
      new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
      j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
      'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
      })(window,document,'script','dataLayer','{{ $gtm }}');
    </script>
  @endif

  {{-- Fonts: Inter + Orbitron (lazy CSS load) --}}
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

  {{-- Swiper CSS (once) --}}
  <link rel="preload"
        href="https://unpkg.com/swiper@10.3.1/swiper-bundle.min.css"
        as="style" onload="this.onload=null;this.rel='stylesheet'">
  <noscript><link rel="stylesheet"
        href="https://unpkg.com/swiper@10.3.1/swiper-bundle.min.css"></noscript>

  {{-- Your app CSS/JS --}}
  @vite(['resources/css/app.css', 'resources/js/app.js'])

  {{-- Swiper JS (once, defer) --}}
  <script defer src="https://unpkg.com/swiper@10.3.1/swiper-bundle.min.js"></script>

  {{-- Iubenda (only if used) --}}
  <script async src="https://embeds.iubenda.com/widgets/4ba02f66-006a-4b4e-85e2-42db144dcce2.js"></script>

  {{-- Optional extra CSS: include only if present in Vite manifest --}}
@php
  $viteManifestPath = public_path('build/manifest.json');
  $viteHasOptional = false;
  if (is_file($viteManifestPath)) {
      $mf = json_decode(file_get_contents($viteManifestPath), true) ?: [];
      $viteHasOptional = array_key_exists('resources/css/optional.css', $mf);
  }
@endphp
@if($viteHasOptional)
  <link rel="preload"
        href="{{ Vite::asset('resources/css/optional.css') }}"
        as="style" onload="this.onload=null;this.rel='stylesheet'">
  <noscript>
    <link rel="stylesheet" href="{{ Vite::asset('resources/css/optional.css') }}">
  </noscript>
@endif

  {{-- Theme bootstrap (kept) --}}
  <script>
  (function () {
    try {
      var d=document.documentElement;
      var cookieTheme=(document.cookie.match(/(?:^|;\s*)theme=([^;]+)/)||[,''])[1].toLowerCase();
      var lsTheme=(localStorage.getItem('theme')||'').toLowerCase();
      var mode=cookieTheme||lsTheme||'system';
      if(!/^(light|dark|system)$/.test(mode)) mode='system';
      var prefersDark=false; try{prefersDark=window.matchMedia&&window.matchMedia('(prefers-color-scheme: dark)').matches;}catch(e){}
      d.classList.toggle('dark', mode==='dark'||(mode==='system'&&prefersDark));
      window.setTheme=function(next){
        next=(next||'system').toLowerCase();
        if(!/^(light|dark|system)$/.test(next)) next='system';
        localStorage.setItem('theme',next);
        document.cookie='theme='+next+'; Path=/; Max-Age=31536000; SameSite=Lax';
        var pref=false; try{pref=window.matchMedia&&window.matchMedia('(prefers-color-scheme: dark)').matches;}catch(e){}
        d.classList.toggle('dark', next==='dark'||(next==='system'&&pref));
      };
      window.toggleTheme=function(){
        var cur=(localStorage.getItem('theme')||'system').toLowerCase();
        setTheme(cur==='dark'?'light':'dark');
      };
    } catch(e){}
  })();
  </script>

  {{-- Theme colors (keep) --}}
  @php $s = \App\Models\SiteSetting::first(); @endphp
  <style>
    :root{
      --bg-light: {{ $s->color_light_bg ?? '#f8fafc' }};
      --accent:   {{ $s->color_accent   ?? '#4f46e5' }};
    }
    .dark{ --bg-dark:  {{ $s->color_dark_bg  ?? '#0b0f1a' }}; }
  </style>

  {{-- Iubenda extra head code (if any) --}}
  @if(($privacySettings?->banner_enabled) && $privacySettings?->banner_head_code)
    {!! $privacySettings->banner_head_code !!}
  @endif
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
@endif
  </body>
</html>
