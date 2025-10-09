@php
  use Illuminate\Support\Facades\Route;

  $s = \App\Models\SiteSetting::first();
  $brand      = $s->brand_name ?? 'TAKE YOUR BASE';
  $discordUrl = $s->discord_link ?? '#';

  $links = [
    ['label'=>'Home',     'route'=>'home',            'match'=>['home']],
    ['label'=>'About',    'route'=>'about',           'match'=>['about']],
    ['label'=>'Packs',    'route'=>'packs.public',    'match'=>['packs.*']],
    ['label'=>'Services', 'route'=>'services.public', 'match'=>['services.public']],
    ['label'=>'Builders', 'route'=>'builders.index',  'match'=>['builders.index','builders.show']],
    ['label'=>'Coaches',  'route'=>'coaches.index',   'match'=>['coaches.index','coaches.show']],
    ['label'=>'Contacts', 'route'=>'contacts',        'match'=>['contacts']],
  ];

  $cartCount = $cartCount ?? (\App\Support\Cart::count() ?? 0);

  $isAdmin = auth()->check() && strtolower((string)(auth()->user()->role ?? '')) === 'admin';

  $ff = \App\Support\FeatureFlags::all();
  $showDiscordExtras = !empty($ff['discord_integration']);
@endphp

<style>
  [x-cloak]{display:none!important}
  /* Forza layout "mobile" in range personalizzato */
  @media (min-width: 500px) and (max-width: 1100px) {
    .nav-desktop, .actions-desktop { display: none !important; }
    .hamburger { display: inline-flex !important; }
  }
</style>

<header
  x-data="{
    dark: document.documentElement.classList.contains('dark'),
    open: false,
    moreOpen: false,
    init(){
      window.addEventListener('storage', (e) => {
        if (e.key === 'theme') this.dark = document.documentElement.classList.contains('dark');
      });
      if (window.matchMedia) {
        const mq = window.matchMedia('(prefers-color-scheme: dark)');
        mq.addEventListener('change', () => {
          const m = (localStorage.getItem('theme') || 'system').toLowerCase();
          if (m === 'system') this.dark = document.documentElement.classList.contains('dark');
        });
      }
    },
    set(t){ setTheme(t); this.dark = document.documentElement.classList.contains('dark'); },
    toggle(){
      const cur = (localStorage.getItem('theme') || 'system').toLowerCase();
      this.set(cur === 'dark' ? 'light' : 'dark');
    }
  }"
  class="sticky top-0 z-50 border-b bg-white/80 backdrop-blur dark:bg-gray-900/70 dark:border-gray-800"
>

  <!-- TOP BAR -->
  <div class="mx-auto flex h-auto max-w-7xl items-center justify-between py-3 px-3 sm:px-6 lg:px-8">

    {{-- Brand --}}
    <a href="{{ route('home') }}" class="flex items-center gap-2 font-semibold tracking-wide">
      @if($s?->logo_light_path || $s?->logo_dark_path)
        <img src="{{ $s?->logo_light_path ? Storage::url($s->logo_light_path) : '' }}" class="h-[60px] w-auto dark:hidden" alt="Logo">
        <img src="{{ $s?->logo_dark_path ? Storage::url($s->logo_dark_path) : '' }}" class="hidden h-[60px] w-auto dark:block" alt="Logo Dark">
      @else
        {{ $brand }}
      @endif
    </a>

    {{-- Center links (desktop) --}}
    <nav class="hidden items-center gap-5 text-sm sm:flex nav-desktop">
      @foreach($links as $l)
        @php $active = request()->routeIs(...$l['match']); @endphp
        <a href="{{ route($l['route']) }}"
           class="relative pb-0.5 hover:opacity-90 {{ $active ? 'text-[var(--accent)] font-medium' : '' }}">
          {{ $l['label'] }}
          @if($active)
            <span class="absolute -bottom-0.5 left-0 h-0.5 w-full rounded bg-[var(--accent)]"></span>
          @endif
        </a>
      @endforeach

      {{-- More (desktop) --}}
      @if($showDiscordExtras)
        <div class="relative" @mouseenter="moreOpen=true" @mouseleave="moreOpen=false">
          <button type="button"
                  @click="moreOpen = !moreOpen"
                  class="inline-flex items-center gap-1 rounded px-2 py-1 hover:bg-black/5 dark:hover:bg-white/10">
            More
            <svg xmlns="http://www.w3.org/2000/svg" class="h-[14px] w-[14px]" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M6 9l6 6 6-6"/></svg>
          </button>

          <div x-cloak x-show="moreOpen" x-transition.origin.top.left
               class="absolute left-0 mt-2 w-56 rounded-lg border bg-white p-1.5 shadow-lg dark:border-gray-800 dark:bg-gray-900">
            <a href="{{ route('announcements') }}"
               class="block rounded px-3 py-2 text-sm hover:bg-gray-100 dark:hover:bg-gray-800">
              Announcements
            </a>
            <a href="{{ route('feedback') }}"
               class="block rounded px-3 py-2 text-sm hover:bg-gray-100 dark:hover:bg-gray-800">
              Customer Feedback
            </a>
          </div>
        </div>
      @endif
    </nav>

    {{-- Right actions (desktop) --}}
    <div class="hidden items-center gap-3 text-sm sm:flex actions-desktop">

      {{-- Server --}}
      @php $discordUrlBtn = $s->discord_url ?? '#'; @endphp
      <a href="{{ $discordUrlBtn }}"
         class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-white hover:opacity-90"
         style="background: var(--accent);">
         <span>Our server</span>
      </a>

      {{-- Admin --}}
      @if($isAdmin && Route::has('admin.dashboard'))
        <a href="{{ route('admin.dashboard') }}"
           class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-white hover:opacity-90"
           style="background: var(--accent);">
          Admin
        </a>
      @endif

      {{-- Cart --}}
      <a href="{{ route('cart.index') }}" class="relative inline-flex items-center">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
          <path d="M6 6h15l-1.5 9h-12L6 6z"/><circle cx="9" cy="20" r="1"/><circle cx="18" cy="20" r="1"/>
        </svg>
        @if($cartCount > 0)
          <span class="absolute -top-2 -right-2 rounded-full bg-[var(--accent)] px-1.5 text-[10px] leading-4 text-white">
            {{ $cartCount }}
          </span>
        @endif
      </a>

      {{-- Theme --}}
      <button @click="toggle" class="inline-flex items-center p-1.5 hover:opacity-80" title="Theme">
        <svg x-show="!dark" xmlns="http://www.w3.org/2000/svg" class="h-[18px] w-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="12" cy="12" r="4" stroke-width="1.6"/><path d="M12 2v2m0 16v2m10-10h-2M4 12H2m15.5 6.5-1.4-1.4M7.9 7.9 6.5 6.5m10 0-1.4 1.4M7.9 16.1l-1.4 1.4" stroke-width="1.6"/></svg>
        <svg x-show="dark" xmlns="http://www.w3.org/2000/svg" class="h-[18px] w-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M21 12.79A9 9 0 1 1 11.21 3a7 7 0 0 0 9.79 9.79Z" stroke-width="1.6"/></svg>
      </button>

      {{-- Profile / Auth --}}
      @auth
        <a href="{{ route('profile.edit') }}" class="inline-flex items-center gap-1 hover:opacity-80">
          <img src="{{ auth()->user()->avatar_url ?? 'https://www.gravatar.com/avatar/'.md5(strtolower(trim(auth()->user()->email))).'?s=64&d=identicon' }}"
               class="h-6 w-6 rounded-full object-cover" alt="">
        </a>
        <form method="POST" action="{{ route('logout') }}" class="inline">
          @csrf
          <button class="hover:opacity-80">Logout</button>
        </form>
      @else
        <a href="{{ route('login') }}" class="hover:opacity-80">Login</a>
      @endauth
    </div>

    {{-- Hamburger (mobile) --}}
    <button
      @click="open = !open"
      :aria-expanded="open ? 'true' : 'false'"
      aria-controls="mobileNav"
      class="hamburger sm:hidden inline-flex h-10 w-10 items-center justify-center rounded-xl
             bg-white/5 ring-1 ring-black/5 hover:bg-white/10 transition
             dark:bg-white/5 dark:ring-white/10"
      aria-label="Open menu">
      <svg x-show="!open" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
        <path d="M3 6h18M3 12h18M3 18h18" stroke-width="1.8"/>
      </svg>
      <svg x-show="open" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
        <path d="M18 6L6 18M6 6l12 12" stroke-width="1.8"/>
      </svg>
    </button>
  </div>
  <!-- /TOP BAR -->

  {{-- MOBILE PANEL (drawer + backdrop) --}}
  {{-- MOBILE PANEL (drawer + backdrop) --}}
<div
  x-show="open"
  x-transition.opacity
  x-cloak
  @keydown.escape.window="open=false"
  x-effect="$nextTick(() => { document.body.style.overflow = open ? 'hidden' : '' })"
  class="sm:hidden fixed inset-0 z-[60]"
  role="dialog"
  aria-modal="true">

  <!-- Backdrop -->
  <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="open=false"></div>

  <!-- Drawer -->
  <aside id="mobileNav"
         x-show="open"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="translate-x-full opacity-0"
         x-transition:enter-end="translate-x-0 opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="translate-x-0 opacity-100"
         x-transition:leave-end="translate-x-full opacity-0"
         class="absolute right-0 top-0 h-full min-h-full w-[86vw] max-w-sm
                flex flex-col overflow-hidden
                border-l border-white/10 bg-white/10 backdrop-blur-xl
                shadow-2xl ring-1 ring-black/10
                dark:border-white/10 dark:bg-white/5 dark:ring-white/10">

    <!-- Header -->
    <div class="flex items-center justify-between px-4 py-3 shrink-0">
      <div class="flex items-center gap-2">
        @if($s?->logo_light_path || $s?->logo_dark_path)
          <img src="{{ $s?->logo_light_path ? Storage::url($s->logo_light_path) : '' }}" class="h-7 w-auto dark:hidden" alt="Logo">
          <img src="{{ $s?->logo_dark_path ? Storage::url($s->logo_dark_path) : '' }}" class="hidden h-7 w-auto dark:block" alt="Logo Dark">
        @else
          <span class="text-sm font-semibold tracking-wide">{{ $brand }}</span>
        @endif
      </div>
      <div class="flex items-center gap-1.5">
        <button @click="toggle" class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-white/5 ring-1 ring-black/5 hover:bg-white/10 dark:ring-white/10" aria-label="Theme">
          <svg x-show="!dark" xmlns="http://www.w3.org/2000/svg" class="h-[18px] w-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <circle cx="12" cy="12" r="4" stroke-width="1.6"/><path d="M12 2v2m0 16v2m10-10h-2M4 12H2m15.5 6.5-1.4-1.4M7.9 7.9 6.5 6.5m10 0-1.4 1.4M7.9 16.1l-1.4 1.4" stroke-width="1.6"/>
          </svg>
          <svg x-show="dark" xmlns="http://www.w3.org/2000/svg" class="h-[18px] w-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path d="M21 12.79A9 9 0 1 1 11.21 3a7 7 0 0 0 9.79 9.79Z" stroke-width="1.6"/>
          </svg>
        </button>
        <button @click="open=false" class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-white/5 ring-1 ring-black/5 hover:bg-white/10 dark:ring-white/10" aria-label="Close">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-[18px] w-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path d="M18 6L6 18M6 6l12 12" stroke-width="1.8"/>
          </svg>
        </button>
      </div>
    </div>

    <!-- Quick actions -->
    <div class="px-4 pb-3 shrink-0">
      <div class="grid grid-cols-3 gap-2">
        <a href="{{ $discordUrl }}" class="rounded-xl bg-[var(--accent)]/90 px-3 py-2 text-center text-white hover:bg-[var(--accent)] transition" @click="open=false">Server</a>
        <a href="{{ route('cart.index') }}" class="rounded-xl bg-white/10 px-3 py-2 text-center ring-1 ring-white/10 hover:bg-white/20 transition" @click="open=false">
          Cart
          @if($cartCount > 0)
            <span class="ml-1 rounded-full bg-[var(--accent)] px-1.5 text-[10px] leading-4 text-white align-middle">{{ $cartCount }}</span>
          @endif
        </a>
        @if($isAdmin && Route::has('admin.dashboard'))
          <a href="{{ route('admin.dashboard') }}" class="rounded-xl bg-white/10 px-3 py-2 text-center ring-1 ring-white/10 hover:bg-white/20 transition" @click="open=false">Admin</a>
        @else
          <button disabled class="rounded-xl bg-white/5 px-3 py-2 text-center text-gray-400 ring-1 ring-white/10 cursor-default">Menu</button>
        @endif
      </div>
    </div>

    <!-- NAV SCROLL AREA -->
    <nav class="flex-1 overflow-y-auto px-3 pb-6 space-y-1">
      @foreach($links as $l)
        @php $active = request()->routeIs(...$l['match']); @endphp
        <a href="{{ route($l['route']) }}" @click="open=false"
           class="group flex items-center justify-between rounded-xl px-3 py-3 ring-1 ring-white/10 bg-white/5 hover:bg-white/10 transition
                  {{ $active ? 'text-[var(--accent)] font-medium ring-[var(--accent)]/40 bg-white/15' : '' }}">
          <span class="text-[15px]">{{ $l['label'] }}</span>
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 opacity-60 group-hover:opacity-100" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M9 6l6 6-6 6" stroke-width="1.8"/></svg>
        </a>
      @endforeach
    </nav>

    <!-- FOOTER -->
    <div class="border-t border-white/10 px-4 py-3 backdrop-blur shrink-0">
      @auth
        <div class="flex items-center gap-3">
          <img src="{{ auth()->user()->avatar_url ?? 'https://www.gravatar.com/avatar/'.md5(strtolower(trim(auth()->user()->email))).'?s=64&d=identicon' }}" class="h-8 w-8 rounded-full object-cover" alt="">
          <div class="min-w-0">
            <div class="truncate text-sm font-medium">{{ auth()->user()->name }}</div>
            <div class="truncate text-xs opacity-70">{{ auth()->user()->email }}</div>
          </div>
          <form method="POST" action="{{ route('logout') }}" class="ml-auto">@csrf
            <button class="rounded-lg bg-white/10 px-3 py-1.5 text-sm ring-1 ring-white/10 hover:bg-white/20 transition">Logout</button>
          </form>
        </div>
      @else
        <a href="{{ route('login') }}" @click="open=false"
           class="block w-full rounded-lg bg-[var(--accent)]/90 px-3 py-2 text-center text-white ring-1 ring-white/10 hover:bg-[var(--accent)] transition">
          Login
        </a>
      @endauth
    </div>

  </aside>
</div>
</header>