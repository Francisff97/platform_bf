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

<header class="sticky top-0 z-50 border-b bg-white/80 backdrop-blur dark:bg-gray-900/70 dark:border-gray-800">
  <div class="mx-auto flex h-14 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
    <div class="flex items-center gap-2">
      <!-- Mobile: toggle sidebar (header) -->
      <button id="sidebarToggle"
        class="inline-flex items-center justify-center rounded-md p-2 text-gray-900 dark:text-white hover:bg-gray-200 dark:hover:bg-gray-800 sm:hidden"
        aria-label="Apri menu" aria-controls="adminSidebar" aria-expanded="false">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-6 w-6">
          <path fill-rule="evenodd" d="M3.75 5.25a.75.75 0 0 1 .75-.75h15a.75.75 0 0 1 0 1.5h-15a.75.75 0 0 1-.75-.75zm0 6a.75.75 0 0 1 .75-.75h15a.75.75 0 0 1 0 1.5h-15a.75.75 0 0 1-.75-.75zm0 6a.75.75 0 0 1 .75-.75h15a.75.75 0 0 1 0 1.5h-15a.75.75 0 0 1-.75-.75z" clip-rule="evenodd" />
        </svg>
      </button>

      <a href="{{ route('home') }}" class="flex items-center gap-2">
        @if($s?->logo_light_path || $s?->logo_dark_path)
          <img src="{{ $s?->logo_light_path ? Storage::url($s->logo_light_path) : '' }}" class="h-7 w-auto dark:hidden" alt="Logo">
          <img src="{{ $s?->logo_dark_path ? Storage::url($s->logo_dark_path) : '' }}" class="hidden h-7 w-auto dark:block" alt="Logo Dark">
        @else
          <span class="font-bold">Base Forge</span>
        @endif
      </a>
    </div>
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

<div class="flex min-h-[calc(100vh-3.5rem)]">
  @php
    $items = [
      ['label'=>'Dashboard','route'=>'admin.dashboard','match'=>['admin.dashboard']],
      ['label'=>'Packs','route'=>'admin.packs.index','match'=>['admin.packs.*']],
      ['label'=>'Services','route'=>'admin.services.index','match'=>['admin.services.*']],
      ['label'=>'Builders','route'=>'admin.builders.index','match'=>['admin.builders.*']],
      ['label'=>'Coaches','route'=>'admin.coaches.index','match'=>['admin.coaches.*']],
      ['label'=>'Orders','route'=>'admin.orders.index','match'=>['admin.orders.*']],
    ];
  @endphp

  <div id="sidebarBackdrop" class="fixed inset-0 z-40 hidden bg-black/40 sm:hidden"></div>

  <aside id="adminSidebar" class="fixed inset-y-0 left-0 z-50 w-72 sm:w-64 sm:static sm:z-auto
    flex shrink-0 flex-col justify-between border-r bg-white/90 backdrop-blur dark:bg-gray-900/80 dark:border-gray-800
    transition-transform duration-200 ease-in-out -translate-x-full sm:translate-x-0">
    <div>
      <div class="flex items-center justify-between px-5 py-4 text-sm font-semibold">
        <span>Admin Nav</span>
        <button id="sidebarClose" class="inline-flex items-center justify-center rounded-md p-2 text-gray-900 dark:text-white hover:bg-gray-200 dark:hover:bg-gray-800 sm:hidden" aria-label="Chiudi menu">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-6 w-6">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <nav class="px-2 space-y-1 text-sm">
        @foreach($items as $it)
          @php $active = request()->routeIs(...$it['match']); @endphp
          <a href="{{ route($it['route']) }}" class="block rounded px-3 py-2 hover:bg-gray-100 dark:hover:bg-gray-800 {{ $active ? 'bg-gray-100 dark:bg-gray-800 text-[var(--accent)] font-semibold' : '' }}">
            {{ $it['label'] }}
          </a>
        @endforeach
      </nav>
    </div>
  </aside>

  <main class="flex-1">
    <header class="flex items-center justify-between border-b px-6 py-4 dark:border-gray-800">
      <h1 class="text-xl font-semibold">{{ $title }}</h1>
    </header>
    <div class="p-6">
      {{ $slot }}
    </div>
  </main>
</div>

<!-- Floating buttons -->
<button id="fabOpen" aria-label="Apri menu"
  class="fixed bottom-5 right-5 z-[60] sm:hidden rounded-full bg-[var(--accent)] p-4 text-white shadow-lg ring-2 ring-white/70 dark:ring-black/30">
  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-6 w-6">
    <path fill-rule="evenodd" d="M3.75 5.25a.75.75 0 0 1 .75-.75h15a.75.75 0 0 1 0 1.5h-15a.75.75 0 0 1-.75-.75zm0 6a.75.75 0 0 1 .75-.75h15a.75.75 0 0 1 0 1.5h-15a.75.75 0 0 1-.75-.75zm0 6a.75.75 0 0 1 .75-.75h15a.75.75 0 0 1 0 1.5h-15a.75.75 0 0 1-.75-.75z" clip-rule="evenodd" />
  </svg>
</button>

<button id="fabClose" aria-label="Chiudi menu"
  class="fixed bottom-5 right-5 z-[60] sm:hidden hidden rounded-full bg-red-600 p-4 text-white shadow-lg ring-2 ring-white/70 dark:ring-black/30">
  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-6 w-6">
    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
  </svg>
</button>

<script>
document.addEventListener('DOMContentLoaded', function () {
  const aside = document.getElementById('adminSidebar');
  const openBtn = document.getElementById('sidebarToggle');
  const closeBtn = document.getElementById('sidebarClose');
  const backdrop = document.getElementById('sidebarBackdrop');
  const fabOpen = document.getElementById('fabOpen');
  const fabClose = document.getElementById('fabClose');

  function showFab(isOpen) {
    if (isOpen) {
      fabOpen.classList.add('hidden');
      fabClose.classList.remove('hidden');
    } else {
      fabOpen.classList.remove('hidden');
      fabClose.classList.add('hidden');
    }
  }

  function open() {
    aside.classList.remove('-translate-x-full');
    backdrop.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
    showFab(true);
  }

  function close() {
    aside.classList.add('-translate-x-full');
    backdrop.classList.add('hidden');
    document.body.style.overflow = '';
    showFab(false);
  }

  [openBtn, fabOpen].forEach(btn => btn?.addEventListener('click', open));
  [closeBtn, fabClose, backdrop].forEach(btn => btn?.addEventListener('click', close));

  // Gestisci resize
  const mq = window.matchMedia('(min-width: 640px)');
  mq.addEventListener('change', e => {
    if (e.matches) {
      aside.classList.remove('-translate-x-full');
      backdrop.classList.add('hidden');
      fabOpen.classList.add('hidden');
      fabClose.classList.add('hidden');
    } else {
      close();
    }
  });

  // Stato iniziale mobile
  if (window.matchMedia('(max-width: 639px)').matches) close();
});
</script>
</body>
</html>