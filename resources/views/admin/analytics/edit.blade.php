<x-admin-layout title="Analytics">
  @php
    $s = $s ?? \App\Models\SiteSetting::first() ?? new \App\Models\SiteSetting();
  @endphp

  @if (session('success'))
    <div class="mb-4 rounded-xl border border-green-300 bg-green-50/80 px-3 py-2 text-sm text-green-800">
      {{ session('success') }}
    </div>
  @endif

  <div class="mb-5 rounded-2xl border bg-white/70 p-4 text-sm text-gray-700 shadow-sm dark:border-gray-800 dark:bg-gray-900/60 dark:text-gray-100">
    Connect <strong>Google Tag Manager</strong> to your site. Paste your container ID (e.g. <code>GTM-XXXXXXX</code>).
  </div>

  <form method="POST" action="{{ route('admin.analytics.update') }}" class="grid max-w-2xl gap-5">
    @csrf

    <div class="rounded-2xl border bg-white/80 p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900/60">
      <label class="block text-sm font-medium">GTM Container ID</label>
      <div class="mt-2 flex items-center gap-2">
        <input type="text" name="gtm_container_id"
               value="{{ old('gtm_container_id', $s->gtm_container_id) }}"
               placeholder="GTM-XXXXXXX"
               class="w-full rounded-lg border px-3 py-2 tracking-wider dark:bg-gray-900 dark:border-gray-700">
        @if($s->gtm_container_id)
          <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-medium text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-200">
            Active
          </span>
        @else
          <span class="rounded-full bg-gray-100 px-2.5 py-1 text-xs font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-300">
            Not set
          </span>
        @endif
      </div>
      @error('gtm_container_id')
        <div class="mt-2 text-xs text-red-600">{{ $message }}</div>
      @enderror>

      <p class="mt-3 text-xs text-gray-500">
        Tip: you can quickly disable analytics by leaving this field empty and saving.
      </p>
    </div>

    <div class="flex items-center gap-3">
      <button class="rounded-lg bg-[var(--accent)] px-4 py-2 text-white hover:opacity-90">Save</button>
      <a href="{{ route('admin.dashboard') }}" class="text-sm underline">Cancel</a>
    </div>
  </form>

  {{-- Tiny helper: optional client-side format hint (no validation change server-side) --}}
  <script>
    (function(){
      const input = document.querySelector('input[name="gtm_container_id"]');
      if (!input) return;
      input.addEventListener('blur', () => {
        const v = input.value.trim();
        if (v && !/^GTM-[A-Z0-9]+$/i.test(v)) {
          input.classList.add('ring-2','ring-red-400');
          setTimeout(()=>input.classList.remove('ring-2','ring-red-400'), 1200);
        }
      });
    })();
  </script>
</x-admin-layout>