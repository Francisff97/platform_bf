<x-guest-layout>
  <div class="mx-auto max-w-[520px] px-4">
    <div class="relative rounded-2xl border px-6 py-6 shadow-xl backdrop-blur-xl
                border-black/10 bg-white/60
                dark:border-white/10 dark:bg-white/5">
      <div class="pointer-events-none absolute -inset-1 rounded-2xl opacity-30 blur-2xl"
           style="background: radial-gradient(120px 80px at 18% -10%, var(--accent), transparent 60%);"></div>

      <h1 class="mb-1 text-2xl font-semibold tracking-tight">Confirm password</h1>
      <p class="mb-5 text-sm opacity-80">
        For security reasons, please confirm your password before continuing.
      </p>

      @if ($errors->any())
        <div class="mb-4 rounded-lg border border-red-300 bg-red-50 px-3 py-2 text-sm text-red-700
                    dark:border-red-800 dark:bg-red-900/30 dark:text-red-200">
          {{ $errors->first() }}
        </div>
      @endif

      <form method="POST" action="{{ route('password.confirm') }}" x-data="{show:false}">
        @csrf
        <label for="password" class="block text-sm opacity-90">Password</label>
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

        <div class="mt-5 flex items-center justify-between">
          <a href="{{ url()->previous() }}" class="text-sm opacity-80 underline-offset-2 hover:underline">Back</a>
          <button class="rounded-xl bg-[var(--accent)] px-4 py-2.5 text-white shadow-sm ring-1 ring-white/10 hover:opacity-95">
            Confirm
          </button>
        </div>
      </form>
    </div>
  </div>
</x-guest-layout>