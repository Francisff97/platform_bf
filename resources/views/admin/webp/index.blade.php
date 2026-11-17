<x-admin-layout title="WebP Debug & Regeneration">
  {{-- Flash --}}
  @if (session('success'))
    <div class="mb-4 rounded-lg border border-green-300 bg-green-50/70 px-3 py-2 text-sm text-green-800 dark:border-green-800 dark:bg-green-900/30 dark:text-green-100">
      {{ session('success') }}
    </div>
  @endif
  @if (session('error'))
    <div class="mb-4 rounded-lg border border-red-300 bg-red-50/70 px-3 py-2 text-sm text-red-700 dark:border-red-800 dark:bg-red-900/30 dark:text-red-100">
      {{ session('error') }}
    </div>
  @endif

  {{-- Intro / Stats + Actions --}}
  <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <div class="text-sm opacity-80">
      <div class="font-semibold">WebP status</div>
      <div>Originali: <strong>{{ $total }}</strong> · Con WebP: <strong>{{ $have }}</strong> · Mancanti: <strong>{{ $miss }}</strong></div>
    </div>
    <div class="flex gap-2">
      <form method="POST" action="{{ route('admin.webp.rebuild') }}" x-data="{loading:false}" @submit="loading=true">
        @csrf
        <input type="hidden" name="only_missing" value="1">
        <button :disabled="loading"
                class="rounded-xl bg-[var(--accent)] px-4 py-2 text-white hover:opacity-90">
          <span x-show="!loading">Generate missing WebP</span>
          <span x-show="loading">Working…</span>
        </button>
      </form>
      <form method="POST" action="{{ route('admin.webp.rebuild') }}"
            onsubmit="return confirm('Rigenerare TUTTI i WebP? (Sovrascrive anche quelli esistenti)')">
        @csrf
        <input type="hidden" name="only_missing" value="0">
        <button class="rounded-xl border px-4 py-2 ring-1 ring-black/10 hover:bg-black/5 dark:ring-white/10 dark:hover:bg-white/10">
          Rebuild all
        </button>
      </form>
    </div>
  </div>

  {{-- Mobile cards --}}
  <div class="grid gap-3 sm:hidden">
    @forelse ($images as $img)
      <div class="rounded-2xl border bg-white/70 p-3 shadow-sm backdrop-blur dark:border-gray-800 dark:bg-gray-900/70">
        <div class="mb-2 text-xs opacity-80 truncate">{{ $img['file'] }}</div>
        <div class="grid grid-cols-2 gap-3">
          <div>
            <div class="mb-1 text-xs opacity-70">Original</div>
            <img src="{{ $img['url_original'] }}" class="w-full rounded-lg" loading="lazy" alt="">
          </div>
          <div>
            <div class="mb-1 text-xs opacity-70">WebP</div>
            @if($img['url_webp'])
              <img src="{{ $img['url_webp'] }}" class="w-full rounded-lg" loading="lazy" alt="">
            @else
              <div class="grid h-[90px] place-items-center rounded-lg bg-gray-100 text-xs text-gray-500 dark:bg-gray-800 dark:text-gray-300">missing</div>
            @endif
          </div>
        </div>
        <div class="mt-2 text-right text-xs">
          @if($img['exists_webp'])
            <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-100">✓ webp</span>
          @else
            <span class="rounded-full bg-red-100 px-2 py-0.5 text-red-700 dark:bg-red-900/30 dark:text-red-100">✗ missing</span>
          @endif
        </div>
      </div>
    @empty
      <div class="rounded-xl border bg-white/70 p-4 text-center text-sm text-gray-600 dark:border-gray-800 dark:bg-gray-900/70 dark:text-gray-300">
        Nessuna immagine trovata in <code>storage/app/public</code>.
      </div>
    @endforelse
  </div>

  {{-- Desktop table --}}
  <div class="hidden overflow-hidden rounded-2xl border bg-white/70 shadow-sm backdrop-blur dark:border-gray-800 dark:bg-gray-900/70 sm:block">
    <table class="min-w-full text-sm">
      <thead class="bg-gray-50/70 text-left text-xs font-semibold uppercase tracking-wide text-gray-600 dark:bg-gray-900/60 dark:text-gray-400">
        <tr>
          <th class="px-3 py-2">File</th>
          <th class="px-3 py-2">Original</th>
          <th class="px-3 py-2">WebP</th>
          <th class="px-3 py-2">State</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-100/70 dark:divide-gray-800">
        @forelse ($images as $img)
          <tr class="align-top hover:bg-black/5 dark:hover:bg-white/5">
            <td class="px-3 py-2 align-middle">
              <div class="max-w-[420px] truncate">{{ $img['file'] }}</div>
            </td>
            <td class="px-3 py-2">
              <img src="{{ $img['url_original'] }}" class="max-h-24 rounded-lg" loading="lazy" alt="">
            </td>
            <td class="px-3 py-2">
              @if($img['url_webp'])
                <img src="{{ $img['url_webp'] }}" class="max-h-24 rounded-lg" loading="lazy" alt="">
              @else
                <span class="text-xs opacity-60">—</span>
              @endif
            </td>
            <td class="px-3 py-2 align-middle">
              @if($img['exists_webp'])
                <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-xs text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-100">Present</span>
              @else
                <span class="rounded-full bg-red-100 px-2 py-0.5 text-xs text-red-700 dark:bg-red-900/30 dark:text-red-100">Missing</span>
              @endif
            </td>
          </tr>
        @empty
          <tr><td colspan="4" class="px-3 py-6 text-center text-gray-500 dark:text-gray-300">No images found.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</x-admin-layout>