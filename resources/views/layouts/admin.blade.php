@props(['title' => 'Admin'])

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
  {{-- HEADER: se hai il componente <x-site-nav />, usa quello.
       Altrimenti lascia l'header inline qui sotto (scommentalo e togli <x-site-nav />). --}}
  @if (view()->exists('components.site-nav'))
    <x-site-nav />
  @else
  <header class="sticky top-0 z-50 border-b bg-white/80 backdrop-blur dark:bg-gray-900/70 dark:border-gray-800">
    <div class="mx-auto flex h-14 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
      <a href="{{ route('home') }}" class="flex items-center gap-2">
        @if($s?->logo_light_path || $s?->logo_dark_path)
          <img src="{{ $s?->logo_light_path ? Storage::url($s->logo_light_path) : '' }}" class="h-7 w-auto dark:hidden" alt="Logo">
          <img src="{{ $s?->logo_dark_path ? Storage::url($s->logo_dark_path) : '' }}" class="hidden h-7 w-auto dark:block" alt="Logo Dark">
        @else
          <span class="font-bold">Blueprint</span>
        @endif
      </a>
      <nav class="hidden gap-6 text-sm sm:flex">
        <a href="{{ route('packs.public') }}" class="hover:opacity-90 {{ request()->routeIs('packs.*') ? 'text-[var(--accent)] font-medium' : '' }}">Packs</a>
        <a href="{{ route('builders.index') }}" class="hover:opacity-90 {{ request()->routeIs('builders.*') ? 'text-[var(--accent)] font-medium' : '' }}">Builders</a>
        <a href="{{ route('services.public') }}" class="hover:opacity-90 {{ request()->routeIs('services.public') ? 'text-[var(--accent)] font-medium' : '' }}">Services</a>
        <a href="{{ route('contacts') }}" class="hover:opacity-90 {{ request()->routeIs('contacts') ? 'text-[var(--accent)] font-medium' : '' }}">Contact</a>
        <a href="{{ route('admin.dashboard') }}" class="rounded bg-[var(--accent)] px-3 py-1.5 text-white">Admin</a>
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
    {{-- SIDEBAR con active state + profile card --}}
    @php
      $items = [
        ['label'=>'Dashboard',  'route'=>'admin.dashboard',        'match'=>['admin.dashboard']],
        ['label'=>'Packs',      'route'=>'admin.packs.index',      'match'=>['admin.packs.*']],
        ['label'=>'Services',   'route'=>'admin.services.index',   'match'=>['admin.services.*']],
        ['label'=>'Builders',   'route'=>'admin.builders.index',   'match'=>['admin.builders.*']],
        ['label'=>'Sliders',    'route'=>'admin.slides.index',     'match'=>['admin.slides.*']],
        ['label'=>'Heroes',     'route'=>'admin.heroes.index',     'match'=>['admin.heroes.*']],
        ['label'=>'Appearance', 'route'=>'admin.appearance.edit',  'match'=>['admin.appearance.*']],
      ];
    @endphp

    <aside class="flex w-64 shrink-0 flex-col justify-between border-r bg-white/80 backdrop-blur dark:bg-gray-900/70 dark:border-gray-800">
      <div>
        <div class="px-5 py-4 text-sm font-semibold">Admin</div>
        <nav class="px-2 space-y-1 text-sm">
          @foreach($items as $it)
            @php $active = request()->routeIs(...$it['match']); @endphp
            <a href="{{ route($it['route']) }}"
               class="group relative block rounded px-3 py-2
                      hover:bg-gray-100 dark:hover:bg-gray-800
                      {{ $active ? 'bg-gray-100 dark:bg-gray-800 text-[var(--accent)] font-semibold' : '' }}">
              <span class="absolute left-0 top-0 h-full w-0.5 rounded-r {{ $active ? 'bg-[var(--accent)]' : 'bg-transparent' }}"></span>
              {{ $it['label'] }}
            </a>
          @endforeach
        </nav>
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
            <a href="{{ route('profile.edit') }}" class="text-xs underline">Modifica profilo</a>
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
</body>
</html>
