<x-admin-layout title="Privacy & Cookies">
  @if (session('success'))
    <div class="mb-4 rounded border border-green-300 bg-green-50 px-3 py-2 text-sm text-green-800 dark:border-green-800 dark:bg-green-900/30 dark:text-green-100">
      {{ session('success') }}
    </div>
  @endif

  <div class="mb-4 rounded-2xl border bg-white/70 p-4 text-sm text-gray-600 shadow-sm backdrop-blur
              dark:border-gray-800 dark:bg-gray-900/70 dark:text-gray-300">
    Manage your Privacy Policy, Cookie Policy and the Cookie Banner. You can paste any provider’s code
    (e.g., Iubenda) without needing environment variables.
  </div>

  <form method="POST" action="{{ route('admin.privacy.update') }}" class="grid gap-6 max-w-5xl">
    @csrf

    {{-- Provider (facoltativo) --}}
    <div class="rounded-2xl border bg-white/70 p-4 shadow-sm backdrop-blur dark:border-gray-800 dark:bg-gray-900/70">
      <h2 class="mb-2 text-lg font-semibold">Provider</h2>
      <input type="text" name="provider" value="{{ old('provider',$s->provider) }}"
             placeholder="e.g. Iubenda, Cookiebot, Custom…"
             class="mt-1 w-full rounded-xl border px-3 py-2 ring-1 ring-black/10 dark:border-gray-800 dark:bg-gray-900 dark:text-white dark:ring-white/10">
    </div>

    {{-- Banner Cookie --}}
    <div class="rounded-2xl border bg-white/70 p-4 shadow-sm backdrop-blur dark:border-gray-800 dark:bg-gray-900/70">
      <div class="mb-2 flex items-center justify-between">
        <h2 class="text-lg font-semibold">Cookie banner</h2>
        <label class="inline-flex items-center gap-2 text-sm">
          <input type="checkbox" name="banner_enabled" value="1" @checked(old('banner_enabled',$s->banner_enabled))
                 class="h-4 w-4 rounded border-gray-300 dark:border-gray-700">
          <span>Enabled</span>
        </label>
      </div>
      <div class="grid gap-3 sm:grid-cols-2">
        <div>
          <label class="block text-sm opacity-80">Head snippet (in &lt;head&gt;)</label>
          <textarea name="banner_head_code" rows="6"
                    class="mt-1 w-full rounded-xl border px-3 py-2 font-mono text-xs ring-1 ring-black/10 dark:border-gray-800 dark:bg-gray-900 dark:text-white dark:ring-white/10">{{ old('banner_head_code', $s->banner_head_code) }}</textarea>
        </div>
        <div>
          <label class="block text-sm opacity-80">Body snippet (before &lt;/body&gt;)</label>
          <textarea name="banner_body_code" rows="6"
                    class="mt-1 w-full rounded-xl border px-3 py-2 font-mono text-xs ring-1 ring-black/10 dark:border-gray-800 dark:bg-gray-900 dark:text-white dark:ring-white/10">{{ old('banner_body_code', $s->banner_body_code) }}</textarea>
        </div>
      </div>
    </div>

    {{-- Privacy Policy --}}
    <div class="rounded-2xl border bg-white/70 p-4 shadow-sm backdrop-blur dark:border-gray-800 dark:bg-gray-900/70">
      <div class="mb-2 flex items-center justify-between">
        <h2 class="text-lg font-semibold">Privacy Policy</h2>
        <div class="flex items-center gap-4">
          <label class="inline-flex items-center gap-2 text-sm">
            <input type="checkbox" name="policy_enabled" value="1" @checked(old('policy_enabled',$s->policy_enabled))
                   class="h-4 w-4 rounded border-gray-300 dark:border-gray-700">
            <span>Enabled</span>
          </label>
          <label class="inline-flex items-center gap-2 text-sm">
            <input type="checkbox" name="policy_external" value="1" @checked(old('policy_external',$s->policy_external))
                   class="h-4 w-4 rounded border-gray-300 dark:border-gray-700">
            <span>External URL</span>
          </label>
        </div>
      </div>

      <div class="grid gap-3">
        <input type="url" name="policy_external_url" value="{{ old('policy_external_url',$s->policy_external_url) }}"
               placeholder="https://… (leave blank if inline)"
               class="w-full rounded-xl border px-3 py-2 ring-1 ring-black/10 dark:border-gray-800 dark:bg-gray-900 dark:text-white dark:ring-white/10">
        <textarea name="policy_html" rows="8" placeholder="Paste your Privacy Policy HTML (if not external URL)"
                  class="w-full rounded-xl border px-3 py-2 ring-1 ring-black/10 dark:border-gray-800 dark:bg-gray-900 dark:text-white dark:ring-white/10">{{ old('policy_html',$s->policy_html) }}</textarea>
      </div>
    </div>

    {{-- Cookie Policy --}}
    <div class="rounded-2xl border bg-white/70 p-4 shadow-sm backdrop-blur dark:border-gray-800 dark:bg-gray-900/70">
      <div class="mb-2 flex items-center justify-between">
        <h2 class="text-lg font-semibold">Cookie Policy</h2>
        <div class="flex items-center gap-4">
          <label class="inline-flex items-center gap-2 text-sm">
            <input type="checkbox" name="cookies_enabled" value="1" @checked(old('cookies_enabled',$s->cookies_enabled))
                   class="h-4 w-4 rounded border-gray-300 dark:border-gray-700">
            <span>Enabled</span>
          </label>
          <label class="inline-flex items-center gap-2 text-sm">
            <input type="checkbox" name="cookies_external" value="1" @checked(old('cookies_external',$s->cookies_external))
                   class="h-4 w-4 rounded border-gray-300 dark:border-gray-700">
            <span>External URL</span>
          </label>
        </div>
      </div>

      <div class="grid gap-3">
        <input type="url" name="cookies_external_url" value="{{ old('cookies_external_url',$s->cookies_external_url) }}"
               placeholder="https://… (leave blank if inline)"
               class="w-full rounded-xl border px-3 py-2 ring-1 ring-black/10 dark:border-gray-800 dark:bg-gray-900 dark:text-white dark:ring-white/10">
        <textarea name="cookies_html" rows="8" placeholder="Paste your Cookie Policy HTML (if not external URL)"
                  class="w-full rounded-xl border px-3 py-2 ring-1 ring-black/10 dark:border-gray-800 dark:bg-gray-900 dark:text-white dark:ring-white/10">{{ old('cookies_html',$s->cookies_html) }}</textarea>
      </div>
    </div>

    <div class="rounded-2xl border bg-white/70 p-4 shadow-sm backdrop-blur dark:border-gray-800 dark:bg-gray-900/70">
      <label class="block text-sm opacity-80">Last updated (optional)</label>
      <input type="date" name="last_updated_at" value="{{ old('last_updated_at',$s->last_updated_at) }}"
             class="mt-1 w-56 rounded-xl border px-3 py-2 ring-1 ring-black/10 dark:border-gray-800 dark:bg-gray-900 dark:text-white dark:ring-white/10">
    </div>

    <div>
      <button class="rounded-xl bg-[var(--accent)] px-4 py-2 text-white ring-1 ring-white/10 hover:opacity-95">
        Save settings
      </button>
    </div>
  </form>
</x-admin-layout>