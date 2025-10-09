<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
  <head>
    @php $meta = \App\Support\SeoManager::pageMeta(); @endphp
    @if(\App\Support\SeoManager::enabled())
      @if(!empty($meta['title']))       <title>{{ $meta['title'] }}</title> @endif
      @if(!empty($meta['description'])) <meta name="description" content="{{ $meta['description'] }}"> @endif
      @if(!empty($meta['og_image']))    <meta property="og:image" content="{{ $meta['og_image'] }}"> @endif
    @endif

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

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    {{-- 👉 Theme bootstrap (cookie/localStorage/system) PRIMA dei CSS --}}
    <script>
      (function() {
        try {
          const d = document.documentElement;
          const cookieTheme = (document.cookie.match(/(?:^|;\s*)theme=([^;]+)/)?.[1] || '').toLowerCase();
          const lsTheme = (localStorage.getItem('theme') || '').toLowerCase();
          const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;

          let mode = cookieTheme || lsTheme || 'system';
          if (!['light','dark','system'].includes(mode)) mode = 'system';

          const isDark = mode === 'dark' || (mode === 'system' && prefersDark);
          d.classList.toggle('dark', isDark);

          if (mode === 'system' && window.matchMedia) {
            window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
              d.classList.toggle('dark', e.matches);
            });
          }

          window.setTheme = function(next) {
            if (!['light','dark','system'].includes(next)) next = 'system';
            localStorage.setItem('theme', next);
            document.cookie = 'theme=' + next + '; Path=/; Max-Age=31536000; SameSite=Lax';
            const prefers = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
            d.classList.toggle('dark', next === 'dark' || (next === 'system' && prefers));
          };
        } catch(e){}
      })();
    </script>

    <title>{{ $title ?? config('app.name', 'Blueprint Like') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Swiper (come avevi) --}}
    <link rel="stylesheet" href="https://unpkg.com/swiper@10/swiper-bundle.min.css">
    <script defer src="https://unpkg.com/swiper@10/swiper-bundle.min.js"></script>

    @php $s = \App\Models\SiteSetting::first(); @endphp
    <style>
      :root{
        --bg-light: {{ $s->color_light_bg ?? '#f8fafc' }};
        --accent:   {{ $s->color_accent   ?? '#4f46e5' }};
      }
      .dark{ --bg-dark:  {{ $s->color_dark_bg  ?? '#0b0f1a' }}; }
    </style>
  </head>

  <body class="min-h-screen bg-[var(--bg-light)] dark:bg-[var(--bg-dark)] text-gray-900 dark:text-gray-100 font-orbitron">
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
  </body>
</html>
