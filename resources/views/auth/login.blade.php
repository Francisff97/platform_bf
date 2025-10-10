<x-auth-layout title="Sign in">
  <h1 class="mb-1 text-2xl font-semibold">Welcome back</h1>
  <p class="mb-5 text-sm opacity-80">Sign in to continue.</p>

  @if ($errors->any())
    <div class="mb-4 rounded-lg border border-red-300 bg-red-50 px-3 py-2 text-sm text-red-700 dark:border-red-800 dark:bg-red-900/30 dark:text-red-200">
      {{ $errors->first() }}
    </div>
  @endif

  <form method="POST" action="{{ route('login') }}" x-data="{show:false}">
    @csrf
    <div class="grid gap-3">
      <div>
        <label class="block text-sm opacity-90">Email</label>
        <input type="email" name="email" required autofocus
               class="mt-1 w-full rounded-xl border px-3 py-2 ring-1 ring-black/10
                      dark:bg-gray-900 dark:text-white dark:ring-white/10">
      </div>
      <div>
        <label class="block text-sm opacity-90">Password</label>
        <div class="mt-1 flex items-stretch gap-2">
          <input :type="show ? 'text':'password'" name="password" required
                 class="w-full rounded-xl border px-3 py-2 ring-1 ring-black/10
                        dark:bg-gray-900 dark:text-white dark:ring-white/10">
          <button type="button" @click="show=!show"
                  class="rounded-xl border px-3 ring-1 ring-black/10 dark:ring-white/10">
            <span x-text="show ? 'Hide' : 'Show'"></span>
          </button>
        </div>
      </div>

      <label class="inline-flex items-center gap-2 text-sm">
        <input type="checkbox" name="remember" class="h-4 w-4 rounded border-gray-300 dark:border-gray-700">
        <span>Remember me</span>
      </label>

      <button class="mt-2 rounded-xl bg-[var(--accent)] px-4 py-2.5 text-white hover:opacity-90">
        Sign in
      </button>
    </div>
  </form>

  <x-slot name="extra">
    <div class="mx-auto mt-4 max-w-[440px] text-center text-sm opacity-80">
      Don’t have an account?
      <a href="{{ route('register') }}" class="text-[var(--accent)] underline-offset-2 hover:underline">Create one</a>
      @if (Route::has('password.request'))
        · <a href="{{ route('password.request') }}" class="underline-offset-2 hover:underline">Forgot password?</a>
      @endif
    </div>
  </x-slot>
</x-auth-layout>