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
        ['label'=>'Categories Pack', 'route'=>'admin.categories.index', 'match'=>['admin.categories.*']],
        ['label'=>'About page',      'route'=>'admin.about.index',      'match'=>['admin.about.*']],
        ['label'=>'Services',        'route'=>'admin.services.index',   'match'=>['admin.services.*']],
        ['label'=>'Builders',        'route'=>'admin.builders.index',   'match'=>['admin.builders.*']],
        ['label'=>'Coaches',         'route'=>'admin.coaches.index',    'match'=>['admin.coaches.*']],
        ['label'=>'Sliders',         'route'=>'admin.slides.index',     'match'=>['admin.slides.*']],
        ['label'=>'Hero Sections',   'route'=>'admin.heroes.index',     'match'=>['admin.heroes.*']],
        ['label'=>'Orders',          'route'=>'admin.orders.index',     'match'=>['admin.orders.*']],
        ['label'=>'Appearance',      'route'=>'admin.appearance.edit',  'match'=>['admin.appearance.*']],
        ['label'=>'Platform info',   'route'=>'admin.platform.info',    'match'=>['admin.platform.*']],
        ['label'=>'Google Analytics','route'=>'admin.analytics.edit',   'match'=>['admin.analytics.*']],
      ];

      $features = \App\Support\FeatureFlags::all();
      // Mostra il box Add-ons se almeno un flag è true e se addons (master) è on
      $addonsEnabled = (!empty($features['addons'])) && collect($features)->contains(true);
    @endphp

    <!-- Aggiungo solo un id per pilotare la sidebar da JS -->
    <aside id="adminSidebar" class="flex w-64 shrink-0 flex-col justify-between border-r bg-white/80 backdrop-blur dark:bg-gray-900/70 dark:border-gray-800">
      <div>
        <div class="px-5 py-4 text-sm font-semibold">Admin Nav</div>

        {{-- Menu principale --}}
        <nav class="px-2 space-y-1 text-sm">
          @foreach($items as $it)
            @php $active = request()->routeIs(...$it['match']); @endphp
            <a href="{{ route($it['route']) }}"
               class="group relative block rounded px-3 py-2 hover:bg-gray-100 dark:hover:bg-gray-800
                      {{ $active ? 'bg-gray-100 dark:bg-gray-800 text-[var(--accent)] font-semibold' : '' }}">
              <span class="absolute left-0 top-0 h-full w-0.5 rounded-r {{ $active ? 'bg-[var(--accent)]' : 'bg-transparent' }}"></span>
              {{ $it['label'] }}
            </a>
          @endforeach
        </nav>

        {{-- ADD-ONS --}}
@if(!empty($features['addons']))
  <div class="bg-gray-100 dark:bg-gray-800 my-4 mx-4 rounded-[10px] p-[10px]">
    <div class="px-3 pt-2 text-xs uppercase text-gray-500">Add-ons</div>

    <nav class="px-2 space-y-1 text-sm mt-1">
      {{-- Email Templates --}}
      @if(!empty($features['email_templates']))
        <!-- @php $active = request()->routeIs('admin.addons.email-templates*'); @endphp -->
        <a href="{{ route('admin.addons.email-templates') }}"
           class="group relative block rounded px-3 py-2 hover:bg-gray-100 dark:hover:bg-gray-800
                  {{ $active ? 'bg-gray-100 dark:bg-gray-800 text-[var(--accent)] font-semibold' : '' }}">
          <span class="absolute left-0 top-0 h-full w-0.5 rounded-r {{ $active ? 'bg-[var(--accent)]' : 'bg-transparent' }}"></span>
          Email templates
        </a>
      @endif

      {{-- Discord --}}
      @if(!empty($features['discord_integration']))
        <!-- @php $active = request()->routeIs('admin.addons.discord*'); @endphp -->
        <a href="{{ route('admin.addons.discord') }}"
           class="group relative block rounded px-3 py-2 hover:bg-gray-100 dark:hover:bg-gray-800
                  {{ $active ? 'bg-gray-100 dark:bg-gray-800 text-[var(--accent)] font-semibold' : '' }}">
          <span class="absolute left-0 top-0 h-full w-0.5 rounded-r {{ $active ? 'bg-[var(--accent)]' : 'bg-transparent' }}"></span>
          Discord integration
        </a>
      @endif

      {{-- Tutorials --}}
      @if(!empty($features['tutorials']))
        <!-- @php $active = request()->routeIs('admin.addons.tutorials*'); @endphp -->
        <a href="{{ route('admin.addons.tutorials') }}"
           class="group relative block rounded px-3 py-2 hover:bg-gray-100 dark:hover:bg-gray-800
                  {{ $active ? 'bg-gray-100 dark:bg-gray-800 text-[var(--accent)] font-semibold' : '' }}">
          <span class="absolute left-0 top-0 h-full w-0.5 rounded-r {{ $active ? 'bg-[var(--accent)]' : 'bg-transparent' }}"></span>
          Tutorials
        </a>
      @endif
    </nav>
  </div>
@endif
      </div>

      {{-- PROFILE CARD --}}
      @auth
        @php $u = auth()->user(); @endphp
        <div class="m-3 rounded-xl border p-3 dark:border-gray-800 bg-white/70 dark:bg-gray-900/60">
          <div class="flex items-center gap-3">
            <img
              src="{{ $u->avatar_url ?? 'https://www.gravatar.com/avatar/'.md5(strtolower(trim($u->email))).'?s=128&d=identicon' }}"
              class="h-10 w-10 rounded-full object-cover"
              alt="{{ $u->name }}"
            >
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
        // Applica stile off-canvas SOLO su mobile
        if (isMobile()) {
          Object.assign(aside.style, {
            position: 'fixed',
            top: headerH + 'px',
            left: '0',
            bottom: '0',
            width: aside.offsetWidth ? aside.offsetWidth + 'px' : '16rem', // fallback
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
          // Ripristina comportamento desktop
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
        aside.style.transform = 'translateX(0%))';
        // fix: correct translate to 0
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

      // Eventi
      fabOpen.addEventListener('click', openSidebar);
      fabClose.addEventListener('click', closeSidebar);
      backdrop.addEventListener('click', closeSidebar);

      // Init + on resize
      applyMobileStyles();
      window.addEventListener('resize', applyMobileStyles);
    });
  </script>
</body>
</html>