{{-- resources/views/auth/register.blade.php --}}
<x-guest-layout>
  @php
    $isAdmin = $isAdmin ?? false;
    $action  = $isAdmin ? route('register.admin.store') : route('register');
    $title   = $isAdmin ? 'Create Admin' : 'Create your account';
  @endphp

  <div class="mx-auto max-w-[560px] px-4">
    {{-- Card glass --}}
    <div class="relative rounded-2xl border px-6 py-6 shadow-xl backdrop-blur-xl
                border-black/10 bg-white/60
                dark:border-white/10 dark:bg-white/5">
      {{-- soft glow --}}
      <div class="pointer-events-none absolute -inset-1 rounded-2xl opacity-30 blur-2xl"
           style="background: radial-gradient(120px 80px at 18% -10%, var(--accent), transparent 60%);"></div>

      {{-- Header --}}
      <div class="mb-5 flex items-start justify-between gap-3">
        <div>
          <h1 class="text-2xl font-semibold tracking-tight">{{ $title }}</h1>
          <p class="mt-1 text-sm opacity-80">
            {{ $isAdmin ? 'Set up the first administrator account.' : 'Start selling or booking in minutes.' }}
          </p>
        </div>
        @if($isAdmin)
          <span class="rounded-full bg-[var(--accent)] px-2 py-1 text-[11px] font-semibold text-white">
            Admin setup
          </span>
        @endif
      </div>

      {{-- Errors --}}
      @if ($errors->any())
        <div class="mb-4 rounded-lg border border-red-300 bg-red-50 px-3 py-2 text-sm text-red-700
                    dark:border-red-800 dark:bg-red-900/30 dark:text-red-200">
          {{ $errors->first() }}
        </div>
      @endif

      {{-- Form --}}
      <form method="POST" action="{{ $action }}" x-data="{show:false, show2:false}">
        @csrf

        <div class="grid gap-4">
          {{-- Name --}}
          <div>
            <label for="name" class="block text-sm opacity-90">Full name</label>
            <input id="name" name="name" type="text" value="{{ old('name') }}" required autofocus autocomplete="name"
                   class="mt-1 w-full rounded-xl border px-3 py-2 ring-1 ring-black/10
                          focus:outline-none focus:ring-2 focus:ring-[var(--accent)]
                          dark:bg-gray-900 dark:text-white dark:ring-white/10">
            @error('name')
              <div class="mt-1 text-xs text-red-600 dark:text-red-300">{{ $message }}</div>
            @enderror
          </div>

          {{-- Email --}}
          <div>
            <label for="email" class="block text-sm opacity-90">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="username"
                   class="mt-1 w-full rounded-xl border px-3 py-2 ring-1 ring-black/10
                          focus:outline-none focus:ring-2 focus:ring-[var(--accent)]
                          dark:bg-gray-900 dark:text-white dark:ring-white/10">
            @error('email')
              <div class="mt-1 text-xs text-red-600 dark:text-red-300">{{ $message }}</div>
            @enderror
          </div>

          {{-- Password --}}
          <div>
            <label for="password" class="block text-sm opacity-90">Password</label>
            <div class="mt-1 flex items-stretch gap-2">
              <input :type="show ? 'text' : 'password'" id="password" name="password" required autocomplete="new-password"
                     class="w-full rounded-xl border px-3 py-2 ring-1 ring-black/10
                            focus:outline-none focus:ring-2 focus:ring-[var(--accent)]
                            dark:bg-gray-900 dark:text-white dark:ring-white/10">
              <button type="button" @click="show=!show"
                      class="rounded-xl border px-3 text-sm ring-1 ring-black/10 hover:bg-black/5
                             dark:ring-white/10 dark:hover:bg-white/10">
                <span x-text="show ? 'Hide' : 'Show'"></span>
              </button>
            </div>
            @error('password')
              <div class="mt-1 text-xs text-red-600 dark:text-red-300">{{ $message }}</div>
            @enderror
          </div>

          {{-- Confirm Password --}}
          <div>
            <label for="password_confirmation" class="block text-sm opacity-90">Confirm password</label>
            <div class="mt-1 flex items-stretch gap-2">
              <input :type="show2 ? 'text' : 'password'" id="password_confirmation" name="password_confirmation" required autocomplete="new-password"
                     class="w-full rounded-xl border px-3 py-2 ring-1 ring-black/10
                            focus:outline-none focus:ring-2 focus:ring-[var(--accent)]
                            dark:bg-gray-900 dark:text-white dark:ring-white/10">
              <button type="button" @click="show2=!show2"
                      class="rounded-xl border px-3 text-sm ring-1 ring-black/10 hover:bg-black/5
                             dark:ring-white/10 dark:hover:bg-white/10">
                <span x-text="show2 ? 'Hide' : 'Show'"></span>
              </button>
            </div>
            @error('password_confirmation')
              <div class="mt-1 text-xs text-red-600 dark:text-red-300">{{ $message }}</div>
            @enderror
          </div>

          {{-- Footer actions --}}
          <div class="mt-2 flex items-center justify-between gap-3">
            <a href="{{ route('login') }}"
               class="text-sm opacity-80 underline-offset-2 hover:underline">
              Already registered?
            </a>

            <button class="rounded-xl bg-[var(--accent)] px-4 py-2.5 text-white shadow-sm ring-1 ring-white/10 hover:opacity-95">
              {{ $isAdmin ? 'Create Admin' : 'Register' }}
            </button>
          </div>
        </div>
      </form>
    </div>

    {{-- Extra helper note --}}
    <p class="mx-auto mt-4 max-w-[560px] px-1 text-center text-xs opacity-70">
      By creating an account you agree to our Terms and Privacy Policy.
    </p>
  </div>
</x-guest-layout>