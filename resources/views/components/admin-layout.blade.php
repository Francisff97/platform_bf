@props(['title' => 'Admin Dashboard'])

<!DOCTYPE html>
<html lang="it" class="{{ session('theme','')==='dark' ? 'dark' : '' }}">
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
<div class="w-full bg-orange-400 text-black text-center py-2 font-semibold">
    ⚠️ Demo Mode: Edits and creations not allowed.
</div>
@endif

@if($gtm)
  <!-- Google Tag Manager (noscript) -->
  <noscript><iframe src="https://www.googletagmanager.com/ns.html?id={{ $gtm }}"
  height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
  <!-- End Google Tag Manager (noscript) -->
@endif
  {{-- HEADER --}}
  @if (view()->exists('components.site-nav'))
    <x-site-nav />
  @else
    <header id="siteHeader" class="sticky top-0 z-50 border-b bg-white/80 backdrop-blur dark:bg-gray-900/70 dark:border-gray-800">
      <div class="mx-auto flex h-14 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
        <a href="{{ route('home') }}" class="flex items-center gap-2">
          @if($s?->logo_light_path || $s?->logo_dark_path)
            <img src="{{ $s?->logo_light_path ? Storage::url($s->logo_light_path) : '' }}" class="h-7 w-auto dark:hidden" alt="Logo">
            <img src="{{ $s?->logo_dark_path ? Storage::url($s->logo_dark_path) : '' }}" class="hidden h-7 w-auto dark:block" alt="Logo Dark">
          @else
            <span class="font-bold">Base Forge</span>
          @endif
        </a>
        <nav class="hidden gap-6 text-sm sm:flex">
          <a href="{{ route('packs.public') }}"     class="hover:opacity-90 {{ request()->routeIs('packs.*') ? 'text-[var(--accent)] font-medium' : '' }}">Packs</a>
          <a href="{{ route('builders.index') }}"   class="hover:opacity-90 {{ request()->routeIs('builders.*') ? 'text-[var(--accent)] font-medium' : '' }}">Builders</a>
          <a href="{{ route('services.public') }}"  class="hover:opacity-90 {{ request()->routeIs('services.public') ? 'text-[var(--accent)] font-medium' : '' }}">Services</a>
          <a href="{{ route('coaches.index') }}"    class="hover:opacity-90 {{ request()->routeIs('coaches.*') ? 'text-[var(--accent)] font-medium' : '' }}">Coaches</a>
          <a href="{{ route('contacts') }}"         class="hover:opacity-90 {{ request()->routeIs('contacts') ? 'text-[var(--accent)] font-medium' : '' }}">Contact</a>
          <a href="{{ route('admin.dashboard') }}"  class="rounded bg-[var(--accent)] px-3 py-1.5 text-white">Admin</a>
        </nav>
        @auth
          @php $u = auth()->user(); @endphp
          <a href="{{ route('profile.edit') }}" class="flex items-center gap-2">
            <img
              src="{{ $u->avatar_url ?? 'https://www.gravatar.com/avatar/'.md5(strtolower(trim($u->email))).'?s=64&d=identicon' }}"
              class="h-8 w-8 rounded-full object-cover"
              alt="{{ $u->name }}"
            >
            <span class="hidden text-sm sm:inline">{{ $u->name }}</span>
          </a>
        @endauth
      </div>
    </header>
  @endif

  <div class="flex min-h-[calc(100vh-3.5rem)]">
    {{-- SIDEBAR --}}
    @php
      $items = [
        ['label'=>'Dashboard',       'route'=>'admin.dashboard',        'match'=>['admin.dashboard']],
        ['label'=>'Packs',           'route'=>'admin.packs.index',      'match'=>['admin.packs.*']],
        ['label'=>'Coupons',           'route'=>'admin.coupons.index',      'match'=>['admin.coupons.*']],
        ['label'=>'Categories Pack', 'route'=>'admin.categories.index', 'match'=>['admin.categories.*']],
        ['label'=>'About page',      'route'=>'admin.about.index',      'match'=>['admin.about.*']],
        ['label'=>'Services',        'route'=>'admin.services.index',   'match'=>['admin.services.*']],
        ['label'=>'Builders',        'route'=>'admin.builders.index',   'match'=>['admin.builders.*']],
        ['label'=>'Coaches',         'route'=>'admin.coaches.index',    'match'=>['admin.coaches.*']],
        ['label'=>'Partners',         'route'=>'admin.partners.index',    'match'=>['admin.partners.*']],
        ['label'=>'Sliders',         'route'=>'admin.slides.index',     'match'=>['admin.slides.*']],
        ['label'=>'Hero Sections',   'route'=>'admin.heroes.index',     'match'=>['admin.heroes.*']],
        ['label'=>'Orders',          'route'=>'admin.orders.index',     'match'=>['admin.orders.*']],
        ['label'=>'Appearance',      'route'=>'admin.appearance.edit',  'match'=>['admin.appearance.*']],
        ['label'=>'Platform info',   'route'=>'admin.platform.info',    'match'=>['admin.platform.*']],
        ['label'=>'Privacy e Cookies','route'=>'admin.privacy.edit',   'match'=>['admin.privacy.*']],
        ['label'=>'Google Analytics','route'=>'admin.analytics.edit',   'match'=>['admin.analytics.*']],
      ];

      // ------------------------------
      // SLUG + FLAGS (come richiesto)
      // ------------------------------
       $slug =
      request('installation') ? strtolower(trim(request('installation'))) :
      (optional(\App\Models\SiteSetting::first())->flags_installation_slug
        ? strtolower(trim(optional(\App\Models\SiteSetting::first())->flags_installation_slug))
        : (config('flags.installation_slug') ?: config('flags.default_slug', 'demo')));

  $features = \App\Support\FeatureFlags::all($slug);
  $addonsEnabled = (!empty($features['addons'])) && collect($features)->contains(true);
    @endphp

    <aside id="adminSidebar"
  class="sticky top-[3.5rem] max-h-[calc(100vh-3.5rem)]
         flex w-64 shrink-0 flex-col justify-between border-r bg-white/80 backdrop-blur
         dark:bg-gray-900/70 dark:border-gray-800">

  @php
  // ==== feature flags + route existence ====
  $slug = request('installation')
      ? strtolower(trim(request('installation')))
      : (optional(\App\Models\SiteSetting::first())->flags_installation_slug
          ?: (config('flags.installation_slug') ?: config('flags.default_slug','demo')));

  $features = \App\Support\FeatureFlags::all($slug);

  $hasEmailTemplates = \Illuminate\Support\Facades\Route::has('admin.addons.email-templates');
  $hasDiscord        = \Illuminate\Support\Facades\Route::has('admin.addons.discord');
  $hasTutorials      = \Illuminate\Support\Facades\Route::has('admin.addons.tutorials');

  // Costruisci dinamicamente gli item degli Add-ons in base a FLAGS + route esistenti
  $addOnItems = [];
  if (!empty($features['email_templates']) && $hasEmailTemplates) {
    $addOnItems[] = ['label'=>'Email templates','route'=>'admin.addons.email-templates','match'=>['admin.addons.email-templates*']];
  }
  if (!empty($features['discord_integration']) && $hasDiscord) {
    $addOnItems[] = ['label'=>'Discord integration','route'=>'admin.addons.discord','match'=>['admin.addons.discord*']];
  }
  if (!empty($features['tutorials']) && $hasTutorials) {
    $addOnItems[] = ['label'=>'Videos','route'=>'admin.addons.tutorials','match'=>['admin.addons.tutorials*']];
  }

  // ==== gruppi sidebar (accordion) ====
  $groups = [
    [
      'label' => 'Admin','key'=>'admin',
      'items' => [
        ['label'=>'Dashboard', 'route'=>'admin.dashboard', 'match'=>['admin.dashboard']],
        ['label'=>'Image optimizer', 'route'=>'admin.webp.index', 'match'=>['admin.webp.*']],
      ],
    ],
    [
      'label'=>'Content','key'=>'content',
      'items'=>[
        ['label'=>'Packs', 'route'=>'admin.packs.index', 'match'=>['admin.packs.*']],
        ['label'=>'Categories Pack', 'route'=>'admin.categories.index', 'match'=>['admin.categories.*']],
        ['label'=>'Services', 'route'=>'admin.services.index', 'match'=>['admin.services.*']],
        ['label'=>'About page', 'route'=>'admin.about.index', 'match'=>['admin.about.*']],
      ],
    ],
    [
      'label'=>'People','key'=>'people',
      'items'=>[
        ['label'=>'Builders', 'route'=>'admin.builders.index', 'match'=>['admin.builders.*']],
        ['label'=>'Coaches',  'route'=>'admin.coaches.index',  'match'=>['admin.coaches.*']],
        ['label'=>'Partners', 'route'=>'admin.partners.index', 'match'=>['admin.partners.*']],
      ],
    ],
    [
      'label'=>'Presentation','key'=>'presentation',
      'items'=>[
        ['label'=>'Hero Sections', 'route'=>'admin.heroes.index', 'match'=>['admin.heroes.*']],
        ['label'=>'Sliders', 'route'=>'admin.slides.index', 'match'=>['admin.slides.*']],
      ],
    ],
    [
      'label'=>'Commerce','key'=>'commerce',
      'items'=>[
        ['label'=>'Orders', 'route'=>'admin.orders.index', 'match'=>['admin.orders.*']],
        ['label'=>'Coupons', 'route'=>'admin.coupons.index', 'match'=>['admin.coupons.*']],
        ['label'=>'Customers', 'route'=>'admin.users.index', 'match'=>['admin.users.*']],
      ],
    ],
    [
      'label'=>'SEO','key'=>'seo',
      'items'=>[
        ['label'=>'Pages', 'route'=>'admin.seo.pages.index', 'match'=>['admin.seo.pages.*']],
        ['label'=>'Media', 'route'=>'admin.seo.media.index', 'match'=>['admin.seo.media.*']],
      ],
    ],
    [
      'label'=>'Platform','key'=>'platform',
      'items'=>[
        ['label'=>'Appearance', 'route'=>'admin.appearance.edit', 'match'=>['admin.appearance.*']],
        ['label'=>'Platform info', 'route'=>'admin.platform.info', 'match'=>['admin.platform.*']],
        ['label'=>'Google Analytics','route'=>'admin.analytics.edit', 'match'=>['admin.analytics.*']],
        ['label'=>'Privacy e Cookies','route'=>'admin.privacy.edit', 'match'=>['admin.privacy.*']],
      ],
    ],
    // Aggiungi il gruppo Add-ons SOLO se ci sono item validi
    count($addOnItems) ? ['label'=>'Add-ons','key'=>'addons','items'=>$addOnItems] : null,
  ];

  // rimuovi i gruppi null (quando non ci sono add-ons)
  $groups = array_values(array_filter($groups));
@endphp

  <div class="overflow-y-auto px-2 py-4 space-y-3">
    @foreach($groups as $g)
      @php $isAnyActive = collect($g['items'])->contains(fn($it)=>request()->routeIs(...$it['match'])); @endphp
      <section class="overflow-hidden rounded-xl border bg-white/70 dark:border-gray-800 dark:bg-gray-900/60"
               data-acc-group="{{ $g['key'] }}">
        <button type="button"
                class="flex w-full items-center justify-between px-3 py-2 text-sm font-semibold"
                data-acc-toggle>
          <span class="uppercase tracking-wide text-gray-700 dark:text-gray-300">{{ $g['label'] }}</span>
          <svg class="h-4 w-4 transition-transform {{ $isAnyActive ? 'rotate-180' : '' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M6 9l6 6 6-6"/></svg>
        </button>
        <div class="grid gap-1 px-2 pb-2" data-acc-panel style="display: {{ $isAnyActive ? 'grid' : 'none' }}">
          @foreach($g['items'] as $it)
            @php $active = request()->routeIs(...$it['match']); @endphp
            <a href="{{ route($it['route']) }}"
               class="block rounded px-3 py-2 text-sm hover:bg-gray-100 dark:hover:bg-gray-800
                      {{ $active ? 'bg-gray-100 dark:bg-gray-800 text-[var(--accent)] font-semibold' : '' }}">
              {{ $it['label'] }}
            </a>
          @endforeach
        </div>
      </section>
    @endforeach
  </div>

  {{-- PROFILE CARD --}}
  @auth
    @php $u = auth()->user(); @endphp
    <div class="m-3 rounded-xl border p-3 bg-white/70 dark:border-gray-800 dark:bg-gray-900/60">
      <div class="flex items-center gap-3">
        <img
          src="{{ $u->avatar_url ?? 'https://www.gravatar.com/avatar/'.md5(strtolower(trim($u->email))).'?s=128&d=identicon' }}"
          class="h-10 w-10 rounded-full object-cover"
          alt="{{ $u->name }}">
        <div class="min-w-0">
          <div class="truncate text-sm font-medium">{{ $u->name }}</div>
          <div class="truncate text-xs text-gray-500">{{ $u->email }}</div>
        </div>
      </div>
      <div class="mt-3 flex items-center justify-between">
        <a href="{{ route('profile.edit') }}" class="text-xs underline">Edit profile</a>
        <form method="POST" action="{{ route('logout') }}">@csrf
          <button class="text-xs text-red-600 hover:underline">Logout</button>
        </form>
      </div>
    </div>
  @endauth
</aside>

<script>
  document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-acc-toggle]').forEach(btn => {
      btn.addEventListener('click', () => {
        const panel = btn.closest('[data-acc-group]')?.querySelector('[data-acc-panel]');
        const icon = btn.querySelector('svg');
        const open = panel.style.display !== 'none';
        panel.style.display = open ? 'none' : 'grid';
        if (icon) icon.style.transform = open ? '' : 'rotate(180deg)';
      });
    });
  });
</script>

    {{-- MAIN --}}
    <main class="flex-1">
      <header class="flex items-center justify-between border-b px-6 py-4 dark:border-gray-800">
        <h1 class="text-xl font-semibold">{{ $title }}</h1>
      </header>

      <div class="p-6">
        @if (session('success'))
          <div class="mb-4 rounded border border-green-300 bg-green-50 px-3 py-2 text-sm text-green-800">
            {{ session('success') }}
          </div>
        @endif

        {{ $slot }}
      </div>
    </main>
  </div>

  <!-- Backdrop e Floating Action Buttons (SOLO mobile) -->
  <div id="sidebarBackdrop" class="fixed inset-0 z-40 hidden bg-black/40 sm:hidden" aria-hidden="true"></div>

  <button id="fabOpen" aria-label="Apri menu"
    class="fixed bottom-5 right-5 z-[60] sm:hidden rounded-full bg-[var(--accent)] p-4 text-white shadow-lg ring-2 ring-white/70 dark:ring-black/30">
    <!-- hamburger -->
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-6 w-6">
      <path fill-rule="evenodd" d="M3.75 5.25a.75.75 0 0 1 .75-.75h15a.75.75 0 0 1 0 1.5h-15a.75.75 0 0 1-.75-.75zm0 6a.75.75 0 0 1 .75-.75h15a.75.75 0 0 1 0 1.5h-15a.75.75 0 0 1-.75-.75zm0 6a.75.75 0 0 1 .75-.75h15a.75.75 0 0 1 0 1.5h-15a.75.75 0 0 1-.75-.75z" clip-rule="evenodd" />
    </svg>
  </button>

  <button id="fabClose" aria-label="Chiudi menu"
    class="fixed bottom-5 right-5 z-[60] sm:hidden hidden rounded-full bg-red-600 p-4 text-white shadow-lg ring-2 ring-white/70 dark:ring-black/30">
    <!-- X -->
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-6 w-6">
      <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
    </svg>
  </button>

  <!-- JS: rende la sidebar off-canvas SOLO su mobile, senza toccare markup/classi esistenti -->
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const aside    = document.getElementById('adminSidebar');
      const header   = document.getElementById('siteHeader');
      const backdrop = document.getElementById('sidebarBackdrop');
      const fabOpen  = document.getElementById('fabOpen');
      const fabClose = document.getElementById('fabClose');

      const isMobile = () => window.matchMedia('(max-width: 639px)').matches;

      function applyMobileStyles() {
        const headerH = header?.offsetHeight || 56; // ~3.5rem
        if (isMobile()) {
          Object.assign(aside.style, {
            position: 'fixed',
            top: headerH + 'px',
            left: '0',
            bottom: '0',
            width: aside.offsetWidth ? aside.offsetWidth + 'px' : '16rem',
            maxWidth: '85vw',
            transform: 'translateX(-110%)',
            transition: 'transform 200ms ease-in-out',
            zIndex: '50',
          });
          fabOpen.classList.remove('hidden');
          fabClose.classList.add('hidden');
          backdrop.classList.add('hidden');
          document.body.style.overflow = '';
        } else {
          aside.style.position = '';
          aside.style.top = '';
          aside.style.left = '';
          aside.style.bottom = '';
          aside.style.width = '';
          aside.style.maxWidth = '';
          aside.style.transform = '';
          aside.style.transition = '';
          aside.style.zIndex = '';
          fabOpen.classList.add('hidden');
          fabClose.classList.add('hidden');
          backdrop.classList.add('hidden');
          document.body.style.overflow = '';
        }
      }

      function openSidebar() {
        if (!isMobile()) return;
        aside.style.transform = 'translateX(0%)';
        backdrop.classList.remove('hidden');
        fabOpen.classList.add('hidden');
        fabClose.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
      }

      function closeSidebar() {
        if (!isMobile()) return;
        aside.style.transform = 'translateX(-110%)';
        backdrop.classList.add('hidden');
        fabOpen.classList.remove('hidden');
        fabClose.classList.add('hidden');
        document.body.style.overflow = '';
      }

      fabOpen.addEventListener('click', openSidebar);
      fabClose.addEventListener('click', closeSidebar);
      backdrop.addEventListener('click', closeSidebar);

      applyMobileStyles();
      window.addEventListener('resize', applyMobileStyles);
    });
  </script>
</body>
</html>
