@props(['title' => 'Admin'])

<!DOCTYPE html>
<html lang="it">
<head>
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

  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />

  {{-- 👉 Theme bootstrap PRIMA dei CSS --}}
  <script>
    (function() {
      try {
        const d = document.documentElement;
        const cookieTheme = (document.cookie.match(/(?:^|;\s*)theme=([^;]+)/)?.[1] || '').toLowerCase();
        const lsTheme = (localStorage.getItem('theme') || '').toLowerCase();
        const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
        let mode = cookieTheme || lsTheme || 'system';
        if (!['light','dark','system'].includes(mode)) mode = 'system';
        d.classList.toggle('dark', mode === 'dark' || (mode === 'system' && prefersDark));
        if (mode === 'system' && window.matchMedia) {
          window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', e => {
            d.classList.toggle('dark', e.matches);
          });
        }
        window.setTheme = function(next){
          if (!['light','dark','system'].includes(next)) next = 'system';
          localStorage.setItem('theme', next);
          document.cookie = 'theme='+next+'; Path=/; Max-Age=31536000; SameSite=Lax';
          const prefers = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
          d.classList.toggle('dark', next==='dark' || (next==='system' && prefers));
        };
      } catch(e){}
    })();
  </script>

  <title>{{ $title }}</title>
  @vite(['resources/css/app.css','resources/js/app.js'])

  @php $s = \App\Models\SiteSetting::first(); @endphp
  <style>
    :root{
      --bg-light: {{ $s->color_light_bg ?? '#f8fafc' }};
      --accent:   {{ $s->color_accent   ?? '#4f46e5' }};
    }
    .dark{ --bg-dark: {{ $s->color_dark_bg ?? '#0b0f1a' }}; }
  </style>
</head>

<body class="min-h-screen bg-[var(--bg-light)] dark:bg-[var(--bg-dark)] text-gray-900 dark:text-gray-100">
  @if($gtm)
    <!-- Google Tag Manager (noscript) -->
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id={{ $gtm }}"
    height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
    <!-- End Google Tag Manager (noscript) -->
  @endif

  @if (view()->exists('components.site-nav'))
    <x-site-nav />
  @else
    {{-- …(tuo header fallback, invariato)… --}}
  @endif

  <div class="flex min-h-[calc(100vh-3.5rem)]">
    {{-- …sidebar + main invariati… --}}
    {{ $slot }}
  </div>
</body>
</html>
