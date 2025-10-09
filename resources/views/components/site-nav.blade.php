@php
  use Illuminate\Support\Facades\Route;

  $s = \App\Models\SiteSetting::first();
  $brand      = $s->brand_name ?? 'TAKE YOUR BASE';
  $discordUrl = $s->discord_link ?? '#';

  $links = [
    ['label'=>'Home',     'route'=>'home',            'match'=>['home']],
    ['label'=>'About',    'route'=>'about',           'match'=>['about']],
    ['label'=>'Services', 'route'=>'services.public', 'match'=>['services.public']],
    ['label'=>'Builders', 'route'=>'builders.index',  'match'=>['builders.index','builders.show']],
    ['label'=>'Coaches',  'route'=>'coaches.index',   'match'=>['coaches.index','coaches.show']],
    ['label'=>'Packs',    'route'=>'packs.public',    'match'=>['packs.*']],
    ['label'=>'Contacts', 'route'=>'contacts',        'match'=>['contacts']],
  ];

  $cartCount = $cartCount ?? (\App\Support\Cart::count() ?? 0);

  // flag admin sicuro (auth + role case-insensitive)
  $isAdmin = auth()->check() && strtolower((string)(auth()->user()->role ?? '')) === 'admin';

  // feature flags (per Announcements / Feedback)
  $ff = \App\Support\FeatureFlags::all();
  $showDiscordExtras = !empty($ff['discord_integration']);
@endphp
<style>
/* Forza layout "mobile" tra 768px e 900px */
@media (min-width: 500px) and (max-width: 1100px) {
  .nav-desktop,
  .actions-desktop { display: none !important; }
  .hamburger { display: inline-flex !important; }
}
</style>
<header
 x-data="{
    dark: document.documentElement.classList.contains('dark'),
    open: false,
    moreOpen: false,
    init(){
      // sync quando cambia il tema altrove (altra tab)
      window.addEventListener('storage', (e) => {
        if (e.key === 'theme') this.dark = document.documentElement.classList.contains('dark');
      });
      // sync ai cambi OS se utente è in 'system'
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
  <div class="mx-auto flex h-auto max-w-7xl items-center justify-between py-3 px-3 sm:px-6 lg:px-8">

    {{-- brand --}}
    <a href="{{ route('home') }}" class="flex items-center gap-2 font-semibold tracking-wide">
      @if($s?->logo_light_path || $s?->logo_dark_path)
        <img src="{{ $s?->logo_light_path ? Storage::url($s->logo_light_path) : '' }}" class="h-[60px] w-auto dark:hidden" alt="Logo">
        <img src="{{ $s?->logo_dark_path ? Storage::url($s->logo_dark_path) : '' }}" class="hidden h-[60px] w-auto dark:block" alt="Logo Dark">
      @else
        {{ $brand }}
      @endif
    </a>

    {{-- center links (desktop) --}}
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

      {{-- More (desktop dropdown) --}}
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

    {{-- right actions (desktop) --}}
    <div class="hidden items-center gap-3 text-sm sm:flex actions-desktop">

      {{-- Our server --}}
      @php
        $discordUrlBtn = $s->discord_url ?? '#';
      @endphp
      <a href="{{ $discordUrlBtn }}"
         class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-white hover:opacity-90"
         style="background: var(--accent);">
         <span>Our server</span>
      </a>

      {{-- Admin (solo admin) --}}
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

    {{-- hamburger (mobile) --}}
    <button @click="open = !open" class="sm:hidden inline-flex h-9 w-9 items-center justify-center rounded hover:bg-black/5 dark:hover:bg-white/5 hamburger" aria-label="Menu">
      <svg x-show="!open" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M3 6h18M3 12h18M3 18h18" stroke-width="1.8"/></svg>
      <svg x-show="open" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M18 6L6 18M6 6l12 12" stroke-width="1.8"/></svg>
    </button>
  </div>

  {{-- mobile panel --}}
  <div x-show="open" x-transition.origin.top.left
       @click.outside="open=false"
       class="xl:hidden border-t bg-white/95 backdrop-blur dark:bg-gray-900/90 dark:border-gray-800">
    <div class="mx-auto max-w-7xl px-3 py-3 text-sm">
      {{-- link list --}}
      <nav class="flex flex-col gap-1">
        @foreach($links as $l)
          @php $active = request()->routeIs(...$l['match']); @endphp
          <a href="{{ route($l['route']) }}"
             class="rounded px-3 py-2 hover:bg-gray-100 dark:hover:bg-gray-800 {{ $active ? 'text-[var(--accent)] font-medium' : '' }}"
             @click="open=false">
            {{ $l['label'] }}
          </a>
        @endforeach

        {{-- More (mobile accordion) --}}
        @if($showDiscordExtras)
          <div x-data="{ openMore:false }" class="mt-1">
            <button type="button"
                    @click="openMore = !openMore"
                    class="flex w-full items-center justify-between rounded px-3 py-2 hover:bg-gray-100 dark:hover:bg-gray-800">
              <span>More</span>
              <svg x-show="!openMore" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4"><path d="M6 9l6 6 6-6" stroke="currentColor" fill="none"/></svg>
              <svg x-show="openMore" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4"><path d="M18 15l-6-6-6 6" stroke="currentColor" fill="none"/></svg>
            </button>
            <div x-show="openMore" x-transition.scale.origin.top.left class="mt-1 space-y-1 pl-3">
              <a href="{{ route('announcements') }}"
                 class="block rounded px-3 py-2 hover:bg-gray-100 dark:hover:bg-gray-800"
                 @click="open=false">
                Announcements
              </a>
              <a href="{{ route('feedback') }}"
                 class="block rounded px-3 py-2 hover:bg-gray-100 dark:hover:bg-gray-800"
                 @click="open=false">
                Customer Feedback
              </a>
            </div>
          </div>
        @endif

        {{-- Admin (mobile) --}}
        @if($isAdmin && Route::has('admin.dashboard'))
          <a href="{{ route('admin.dashboard') }}"
             class="rounded px-3 py-2 hover:bg-gray-100 dark:hover:bg-gray-800"
             @click="open=false">Admin</a>
        @endif
      </nav>

      <div class="my-3 h-px w-full bg-gray-200 dark:bg-gray-800"></div>

      {{-- actions --}}
      <div class="flex flex-col gap-2">
        <a href="{{ $discordUrl }}"
           class="inline-flex items-center justify-center gap-2 rounded-full px-3 py-2 text-white"
           style="background: var(--accent);" @click="open=false">
          Our server
        </a>

        <a href="{{ route('cart.index') }}" class="inline-flex items-center justify-center gap-2 rounded border px-3 py-2 hover:bg-gray-50 dark:hover:bg-gray-800" @click="open=false">
          Cart
          @if($cartCount > 0)
            <span class="rounded-full bg-[var(--accent)] px-1.5 text-[10px] leading-4 text-white">{{ $cartCount }}</span>
          @endif
        </a>

        <button class="inline-flex items-center justify-center gap-2 rounded border px-3 py-2 hover:bg-gray-50 dark:hover:bg-gray-800" @click="toggle">
          Theme
        </button>

        @auth
          <a href="{{ route('profile.edit') }}" class="inline-flex items-center justify-center gap-2 rounded border px-3 py-2 hover:bg-gray-50 dark:hover:bg-gray-800" @click="open=false">
            <img src="{{ auth()->user()->avatar_url ?? 'https://www.gravatar.com/avatar/'.md5(strtolower(trim(auth()->user()->email))).'?s=64&d=identicon' }}" class="h-5 w-5 rounded-full object-cover" alt="">
            Profile
          </a>
          <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="inline-flex w-full items-center justify-center gap-2 rounded border px-3 py-2 hover:bg-gray-50 dark:hover:bg-gray-800">
              Logout
            </button>
          </form>
        @else
          <a href="{{ route('login') }}" class="inline-flex items-center justify-center gap-2 rounded border px-3 py-2 hover:bg-gray-50 dark:hover:bg-gray-800" @click="open=false">
            Login
          </a>
        @endauth
      </div>
    </div>
  </div>
</header>
