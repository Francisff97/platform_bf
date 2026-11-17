<x-auth-layout title="Verify your email">
  <h1 class="mb-1 text-2xl font-semibold">Verify your email</h1>
  <p class="mb-4 text-sm opacity-80">
    We sent a verification link to your inbox. Please click it to continue.
  </p>

  @if (session('status') === 'verification-link-sent')
    <div class="mb-4 rounded-lg border border-emerald-300 bg-emerald-50 px-3 py-2 text-sm text-emerald-800 dark:border-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-100">
      A new verification link has been sent to your email address.
    </div>
  @endif

  <div class="flex items-center gap-2">
    <form method="POST" action="{{ route('verification.send') }}">@csrf
      <button class="rounded-xl bg-[var(--accent)] px-4 py-2 text-white hover:opacity-90">
        Resend verification email
      </button>
    </form>

    <form method="POST" action="{{ route('logout') }}">@csrf
      <button class="rounded-xl border px-4 py-2 ring-1 ring-black/10 hover:bg-black/5 dark:ring-white/10 dark:hover:bg-white/10">
        Log out
      </button>
    </form>
  </div>
</x-auth-layout>