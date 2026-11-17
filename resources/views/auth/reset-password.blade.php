<x-guest-layout>
  <div class="mx-auto max-w-[520px] px-4">
    <div class="relative rounded-2xl border px-6 py-6 shadow-xl backdrop-blur-xl
                border-black/10 bg-white/60
                dark:border-white/10 dark:bg-white/5">
      <div class="pointer-events-none absolute -inset-1 rounded-2xl opacity-30 blur-2xl"
           style="background: radial-gradient(120px 80px at 18% -10%, var(--accent), transparent 60%);"></div>

      <h1 class="mb-1 text-2xl font-semibold tracking-tight">Reset password</h1>
      <p class="mb-5 text-sm opacity-80">Choose a new password for your account.</p>

      @if ($errors->any())
        <div class="mb-4 rounded-lg border border-red-300 bg-red-50 px-3 py-2 text-sm text-red-700
                    dark:border-red-800 dark:bg-red-900/30 dark:text-red-200">
          {{ $errors->first() }}
        </div>
      @endif

      <form method="POST" action="{{ route('password.store') }}" x-data="{show:false,show2:false}">
        @csrf
        <input type="hidden" name="token" value="{{ $request->route('token') }}">
        <input type="hidden" name="email" value="{{ old('email', $request->email) }}">

        {{-- Email (readonly) --}}
        <label class="block text-sm opacity-90">Email</label>
        <input type="email" value="{{ old('email', $request->email) }}" disabled
               class="mt-1 w-full cursor-not-allowed rounded-xl border bg-black/5 px-3 py-2 opacity-70 ring-1 ring-black/10
                      dark:bg-white/10 dark:text-white dark:ring-white/10">

        {{-- Password --}}
        <div class="mt-4">
          <label for="password" class="block text-sm opacity-90">New password</label>
          <div class="mt-1 flex items-stretch gap-2">
            <input :type="show ? 'text' : 'password'" id="password" name="password" required
                   class="w-full rounded-xl border px-3 py-2 ring-1 ring-black/10
                          focus:outline-none focus:ring-2 focus:ring-[var(--accent)]
                          dark:bg-gray-900 dark:text-white dark:ring-white/10">
            <button type="button" @click="show=!show"
                    class="rounded-xl border px-3 text-sm ring-1 ring-black/10 hover:bg-black/5
                           dark:ring-white/10 dark:hover:bg-white/10">
              <span x-text="show ? 'Hide' : 'Show'"></span>
            </button>
          </div>
        </div>

        {{-- Confirm --}}
        <div class="mt-4">
          <label for="password_confirmation" class="block text-sm opacity-90">Confirm password</label>
          <div class="mt-1 flex items-stretch gap-2">
            <input :type="show2 ? 'text' : 'password'" id="password_confirmation" name="password_confirmation" required
                   class="w-full rounded-xl border px-3 py-2 ring-1 ring-black/10
                          focus:outline-none focus:ring-2 focus:ring-[var(--accent)]
                          dark:bg-gray-900 dark:text-white dark:ring-white/10">
            <button type="button" @click="show2=!show2"
                    class="rounded-xl border px-3 text-sm ring-1 ring-black/10 hover:bg-black/5
                           dark:ring-white/10 dark:hover:bg:white/10">
              <span x-text="show2 ? 'Hide' : 'Show'"></span>
            </button>
          </div>
        </div>

        <div class="mt-5 flex items-center justify-between">
          <a href="{{ route('login') }}" class="text-sm opacity-80 underline-offset-2 hover:underline">Back to login</a>
          <button class="rounded-xl bg-[var(--accent)] px-4 py-2.5 text-white shadow-sm ring-1 ring-white/10 hover:opacity-95">
            Update password
          </button>
        </div>
      </form>
    </div>
  </div>
</x-guest-layout>