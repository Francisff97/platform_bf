@php
  $s = \App\Models\SiteSetting::first();
  $brand = $s->brand_name ?? 'TAKE YOUR BASE';
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_','-',app()->getLocale()) }}">
<head>
    @php
  // passa qui il soggetto quando serve (es. $pack, $builder, $coach, …)
  $seo = \App\Support\SeoManager::pageMeta(subject: $seoSubject ?? null);
@endphp

@if($seo['title'])       <title>{{ $seo['title'] }}</title> @endif
@if($seo['description']) <meta name="description" content="{{ $seo['description'] }}"> @endif

@if($seo['title'])       <meta property="og:title" content="{{ $seo['title'] }}"> @endif
@if($seo['description']) <meta property="og:description" content="{{ $seo['description'] }}"> @endif
<meta property="og:type" content="website">
<meta property="og:url" content="{{ url()->current() }}">
@if($seo['og_image'])    <meta property="og:image" content="{{ $seo['og_image'] }}"> @endif

<meta name="twitter:card" content="summary_large_image">
@if($seo['og_image'])    <meta name="twitter:image" content="{{ $seo['og_image'] }}"> @endif
  @php $gtm = optional(\App\Models\SiteSetting::first())->gtm_container_id; @endphp
  @if($gtm)
    <!-- Google Tag Manager -->
    <script>
      (function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
      new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
      j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
      'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
      })(window,document,'script','dataLayer','{{ $gtm }}');
    </script>
    <!-- End Google Tag Manager -->
  @endif

  <!-- THEME BOOTSTRAP (safe, no listeners) -->
<script>
(function () {
  try {
    var d = document.documentElement;

    // leggi preferenza salvata
    var cookieTheme = (document.cookie.match(/(?:^|;\s*)theme=([^;]+)/) || [,''])[1].toLowerCase();
    var lsTheme = (localStorage.getItem('theme') || '').toLowerCase();

    // normalizza
    var mode = cookieTheme || lsTheme || 'system';
    if (mode !== 'light' && mode !== 'dark' && mode !== 'system') mode = 'system';

    // preferenza di sistema (senza addEventListener: massima compatibilità)
    var prefersDark = false;
    try { prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches; } catch(e){}

    // applica
    d.classList.toggle('dark', mode === 'dark' || (mode === 'system' && prefersDark));

    // API globali
    window.setTheme = function(next){
      next = (next || 'system').toLowerCase();
      if (next !== 'light' && next !== 'dark' && next !== 'system') next = 'system';
      localStorage.setItem('theme', next);
      document.cookie = 'theme='+next+'; Path=/; Max-Age=31536000; SameSite=Lax';
      var prefers = false;
      try { prefers = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches; } catch(e){}
      d.classList.toggle('dark', next === 'dark' || (next === 'system' && prefers));
    };

    window.toggleTheme = function(){
      var current = (localStorage.getItem('theme') || 'system').toLowerCase();
      var next = current === 'dark' ? 'light' : 'dark';
      setTheme(next);
    };
  } catch(e){}
})();
</script>

  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <title>{{ config('app.name', 'Laravel') }}</title>

  {{-- Fonts (come avevi) --}}
  <link rel="preconnect" href="https://fonts.bunny.net">
  <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

  @vite(['resources/css/app.css', 'resources/js/app.js'])
  @if(($privacySettings?->banner_enabled) && $privacySettings?->banner_head_code)
  {!! $privacySettings->banner_head_code !!}
@endif
</head>
<body class="font-sans text-gray-900 antialiased bg-gray-50 dark:bg-gray-900 dark:text-gray-100">
  @if($gtm)
    <!-- Google Tag Manager (noscript) -->
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id={{ $gtm }}"
    height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
    <!-- End Google Tag Manager (noscript) -->
  @endif

  <div class="mx-auto flex w-full max-w-md items-center justify-end p-4">
    {{-- se vuoi un toggle rapido: --}}
    <button class="rounded border px-3 py-1 text-sm dark:border-gray-700" onclick="setTheme((localStorage.getItem('theme')||'system')==='dark'?'light':'dark')">
      Toggle theme
    </button>
  </div>

  <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100 dark:bg-gray-900">
    <a href="{{ route('home') }}" class="flex items-center gap-2 font-semibold tracking-wide">
      @if($s?->logo_light_path || $s?->logo_dark_path)
        <img src="{{ $s?->logo_light_path ? Storage::url($s->logo_light_path) : '' }}" class="h-20 w-auto dark:hidden" alt="Logo">
        <img src="{{ $s?->logo_dark_path ? Storage::url($s->logo_dark_path) : '' }}" class="hidden h-20 w-auto dark:block" alt="Logo Dark">
      @else
        {{ $brand }}
      @endif
    </a>

    <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg dark:bg-gray-900">
      {{ $slot }}
    </div>
  </div>
  @if(($privacySettings?->banner_enabled) && $privacySettings?->banner_body_code)
  {!! $privacySettings->banner_body_code !!}
@endif
</body>
</html>
