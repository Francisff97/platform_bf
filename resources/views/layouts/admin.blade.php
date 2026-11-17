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
  @if(auth()->check() && auth()->user()->is_demo)
<div class="w-full bg-yellow-400 text-black text-center py-2 font-semibold">
    ⚠️ Modalità Demo: le modifiche non sono consentite.
</div>
@endif
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
