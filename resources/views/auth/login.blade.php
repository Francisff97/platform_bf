<x-auth-layout title="Sign in">
  {{-- === Theme toggle + logo === --}}
  <div x-data="{
        dark: document.documentElement.classList.contains('dark'),
        toggle(){
          const cur = (localStorage.getItem('theme') || 'system').toLowerCase();
          const next = cur === 'dark' ? 'light' : 'dark';
          if(next==='dark'){document.documentElement.classList.add('dark')}
          else{document.documentElement.classList.remove('dark')}
          localStorage.setItem('theme', next);
          this.dark = next==='dark';
        }
      }"
      class="mb-6 flex items-center justify-between">

    {{-- Logo --}}
    <a href="{{ route('home') }}" class="inline-flex items-center gap-2">
      @php $s = \App\Models\SiteSetting::first(); @endphp
      @if($s?->logo_light_path || $s?->logo_dark_path)
        <img src="{{ $s?->logo_light_path ? Storage::url($s->logo_light_path) : '' }}" class="h-8 dark:hidden" alt="Logo">
        <img src="{{ $s?->logo_dark_path ? Storage::url($s->logo_dark_path) : '' }}" class="hidden h-8 dark:block" alt="Logo dark">
      @else
        <span class="text-sm font-semibold tracking-wide dark:text-white">BASE FORGE</span>
      @endif
    </a>

    {{-- Theme toggle --}}
    <button @click="toggle"
            class="inline-flex h-10 w-10 items-center justify-center rounded-xl
                   bg-white/60 ring-1 ring-black/10 hover:bg-white/80
                   dark:bg-white/5 dark:ring-white/10 dark:hover:bg-white/10"
            title="Toggle theme">
      <svg x-show="!dark" xmlns="http://www.w3.org/2000/svg" class="h-[18px] w-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor">
        <circle cx="12" cy="12" r="4" stroke-width="1.6"/>
        <path d="M12 2v2m0 16v2m10-10h-2M4 12H2m15.5 6.5-1.4-1.4M7.9 7.9 6.5 6.5m10 0-1.4 1.4M7.9 16.1l-1.4 1.4" stroke-width="1.6"/>
      </svg>
      <svg x-show="dark" xmlns="http://www.w3.org/2000/svg" class="h-[18px] w-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor">
        <path d="M21 12.79A9 9 0 1 1 11.21 3a7 7 0 0 0 9.79 9.79Z" stroke-width="1.6"/>
      </svg>
    </button>
  </div>

  {{-- === Titolo e intro === --}}
  <h1 class="mb-1 text-2xl font-semibold">Welcome back</h1>
  <p class="mb-5 text-sm opacity-80">Sign in to continue.</p>

  {{-- === Errori === --}}
  @if ($errors->any())
    <div class="mb-4 rounded-lg border border-red-300 bg-red-50 px-3 py-2 text-sm text-red-700
                dark:border-red-800 dark:bg-red-900/30 dark:text-red-200">
      {{ $errors->first() }}
    </div>
  @endif

  {{-- === Form === --}}
  <form method="POST" action="{{ route('login') }}" x-data="{show:false}">
    @csrf

    <div class="grid gap-3">
      {{-- Email --}}
      <div>
        <label class="block text-sm opacity-90 dark:text-gray-300">Email</label>
        <input type="email" name="email" required autofocus
               class="mt-1 w-full rounded-xl border px-3 py-2 ring-1 ring-black/10
                      focus:border-[var(--accent)] focus:ring-2 focus:ring-[var(--accent)]/30
                      dark:border-gray-800 dark:bg-gray-900/70 dark:text-white dark:ring-white/10">
      </div>

      {{-- Password --}}
      <div>
        <label class="block text-sm opacity-90 dark:text-gray-300">Password</label>
        <div class="mt-1 flex items-stretch gap-2">
          <input :type="show ? 'text':'password'" name="password" required
                 class="w-full rounded-xl border px-3 py-2 ring-1 ring-black/10
                        focus:border-[var(--accent)] focus:ring-2 focus:ring-[var(--accent)]/30
                        dark:border-gray-800 dark:bg-gray-900/70 dark:text-white dark:ring-white/10">
          <button type="button" @click="show=!show"
                  class="rounded-xl border px-3 ring-1 ring-black/10 dark:ring-white/10 hover:bg-black/5 dark:hover:bg-white/10">
            <span x-text="show ? 'Hide' : 'Show'"></span>
          </button>
        </div>
      </div>

      {{-- Remember me --}}
      <label class="inline-flex items-center gap-2 text-sm dark:text-gray-300">
        <input type="checkbox" name="remember"
               class="h-4 w-4 rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900">
        <span>Remember me</span>
      </label>

      {{-- Submit --}}
      <button class="mt-2 rounded-xl bg-[var(--accent)] px-4 py-2.5 text-white ring-1 ring-white/10 hover:opacity-90">
        Sign in
      </button>
    </div>
  </form>
  @if(config('demo.enabled') && config('demo.show_banner'))
    <div class="w-full text-black bg-gray-300 text-center py-2 font-semibold dark:bg-gray-800 dark:text-white my-[20px]">
        Want to see the admin part?<br> 
        username: admindemo@base-forge.com<br>
        password: admindemo123
    </div>
@endif
  {{-- === Extra links === --}}
  <x-slot name="extra">
    <div class="mx-auto mt-4 max-w-[440px] text-center text-sm opacity-80 dark:text-gray-300">
      Don’t have an account?
      <a href="{{ route('register') }}" class="text-[var(--accent)] underline-offset-2 hover:underline">Create one</a>
      @if (Route::has('password.request'))
        · <a href="{{ route('password.request') }}" class="underline-offset-2 hover:underline">Forgot password?</a>
      @endif
    </div>
  </x-slot>
</x-auth-layout>