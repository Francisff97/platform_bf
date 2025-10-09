<x-admin-layout title="SEO Media">
  {{-- Top actions: SEARCH + SYNC (collassabili su mobile) --}}
  <div class="mb-4">
    {{-- Toggle visibile SOLO su mobile --}}
    <div class="mb-2 flex items-center justify-between sm:hidden">
      <button id="filtersToggle"
              type="button"
              class="inline-flex items-center gap-2 rounded-lg border px-3 py-2 text-sm hover:bg-gray-50 dark:hover:bg-gray-800 dark:border-gray-800">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-[16px] w-[16px]" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M3 6h18M7 12h10M10 18h4"/></svg>
        Filters
      </button>
      {{-- Prev/Next compatti a sinistra -> su mobile li metto qui in alto --}}
      @php
        $prev = $assets->previousPageUrl();
        $next = $assets->nextPageUrl();
      @endphp
      <div class="flex items-center gap-2">
        <a href="{{ $prev ?: '#' }}"
           class="inline-flex items-center gap-1 rounded-lg border px-3 py-1.5 text-sm
                  {{ $prev ? 'hover:bg-gray-50 dark:hover:bg-gray-800 dark:border-gray-800' : 'pointer-events-none opacity-50 dark:border-gray-800' }}">
          ‹ Prev
        </a>
        <a href="{{ $next ?: '#' }}"
           class="inline-flex items-center gap-1 rounded-lg border px-3 py-1.5 text-sm
                  {{ $next ? 'hover:bg-gray-50 dark:hover:bg-gray-800 dark:border-gray-800' : 'pointer-events-none opacity-50 dark:border-gray-800' }}">
          Next ›
        </a>
      </div>
    </div>

    {{-- Barra filtri (collassabile solo su mobile) --}}
    <div id="filtersBar" class="hidden sm:flex sm:flex-row sm:items-center flex-col gap-2">
      <form method="GET" class="flex w-full max-w-xl items-center gap-2">
        <input name="search" value="{{ request('search') }}" placeholder="Search path…"
               class="h-10 w-full rounded-xl border border-[color:var(--accent)]/40 px-3
                      dark:bg-gray-900 dark:text-white dark:border-gray-800" />
        <button class="h-10 rounded-xl bg-[var(--accent)] px-4 text-white hover:opacity-90">
          Search
        </button>
      </form>

      <form method="POST" action="{{ route('admin.seo.media.sync') }}" class="sm:ml-auto">
        @csrf
        <button class="h-10 rounded-xl border px-4 text-sm hover:bg-gray-50 dark:hover:bg-gray-800 dark:border-gray-800">
          Sync
        </button>
      </form>

      {{-- Su DESKTOP metto qui la paginazione numerica --}}
      <div class="ml-auto hidden sm:block">
        {{ $assets->links() }}
      </div>
    </div>
  </div>

  <form method="POST" action="{{ route('admin.seo.media.bulk') }}"
        class="rounded-2xl border bg-white/70 shadow-sm backdrop-blur dark:bg-gray-900/70 dark:border-gray-800 overflow-hidden">
    @csrf

    {{-- Toolbar (NON sticky) --}}
<div class="flex flex-wrap items-center gap-2 border-b bg-white/80 p-3
            dark:border-gray-800 dark:bg-gray-900/70">
  <label class="inline-flex items-center gap-2 text-sm">
    <input id="checkAll" type="checkbox"
           class="h-4 w-4 rounded border-gray-300 dark:border-gray-700">
    <span>Select all</span>
  </label>

  <div class="ml-auto flex w-full flex-col gap-2 sm:w-auto sm:flex-row sm:items-center">
    <input type="text" name="alt_text" placeholder="Set ALT for selected…"
           class="h-10 w-full sm:w-56 rounded-xl border border-[color:var(--accent)]/40 px-3
                  dark:bg-gray-900 dark:text-white dark:border-gray-800" />
    <label class="inline-flex items-center gap-2 text-sm">
      <input type="checkbox" name="is_lazy" value="1"
             class="h-4 w-4 rounded border-gray-300 dark:border-gray-700">
      <span>Lazy ON</span>
    </label>
    <button class="h-10 rounded-xl bg-[var(--accent)] px-4 text-white hover:opacity-90">
      Apply to selected
    </button>
  </div>
</div>

    {{-- Cards (mobile) --}}
    <div class="grid gap-4 p-4 sm:hidden">
      @forelse($assets as $a)
        <div class="group relative overflow-hidden rounded-2xl border bg-white/80 p-3 shadow-sm
                    dark:border-gray-800 dark:bg-gray-900/70">
          <div class="flex items-start gap-3">
            <input class="rowcheck mt-1.5 h-4 w-4 shrink-0 rounded border-gray-300 dark:border-gray-700"
                   type="checkbox" name="ids[]" value="{{ $a->id }}">

            <div class="h-16 w-16 overflow-hidden rounded-xl ring-1 ring-black/5 dark:ring-white/10">
              <img src="{{ $a->url() }}" class="h-full w-full object-cover" alt="">
            </div>

            <div class="min-w-0 flex-1">
              <div class="truncate text-sm font-medium">{{ $a->path }}</div>
              <div class="mt-0.5 truncate text-xs text-gray-500 dark:text-gray-400">ALT: {{ $a->alt_text ?: '—' }}</div>
              <div class="mt-1">
                <span class="rounded-full px-2 py-0.5 text-[11px] font-semibold
                             {{ $a->is_lazy ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-200'
                                            : 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300' }}">
                  {{ $a->is_lazy ? 'Lazy: Yes' : 'Lazy: No' }}
                </span>
              </div>
            </div>

            <a href="{{ route('admin.seo.media.edit',$a) }}"
               class="ml-2 inline-flex shrink-0 items-center rounded-lg border px-2.5 py-1.5 text-xs
                      hover:bg-gray-50 dark:hover:bg-gray-800 dark:border-gray-700">
              Edit
            </a>
          </div>
        </div>
      @empty
        <div class="rounded-xl border p-6 text-center text-sm text-gray-500 dark:border-gray-800">
          No items.
        </div>
      @endforelse
    </div>

    {{-- Table (md+) --}}
    <div class="hidden sm:block">
      <table class="w-full text-sm">
        <thead class="bg-gray-50 text-left dark:bg-gray-900">
          <tr>
            <th class="px-3 py-2"></th>
            <th class="px-3 py-2">Preview</th>
            <th class="px-3 py-2">Path</th>
            <th class="px-3 py-2">Alt</th>
            <th class="px-3 py-2">Lazy</th>
            <th class="px-3 py-2"></th>
          </tr>
        </thead>
        <tbody>
          @forelse($assets as $a)
            <tr class="border-t align-top hover:bg-gray-50/60 dark:border-gray-800 dark:hover:bg-gray-800/50">
              <td class="px-3 py-2">
                <input class="rowcheck h-4 w-4 rounded border-gray-300 dark:border-gray-700" type="checkbox" name="ids[]" value="{{ $a->id }}">
              </td>
              <td class="px-3 py-2">
                <img src="{{ $a->url() }}" class="h-14 w-14 rounded object-cover" alt="">
              </td>
              <td class="px-3 py-2">{{ $a->path }}</td>
              <td class="px-3 py-2">{{ $a->alt_text }}</td>
              <td class="px-3 py-2">{{ $a->is_lazy ? 'Yes' : 'No' }}</td>
              <td class="px-3 py-2 text-right">
                <a href="{{ route('admin.seo.media.edit',$a) }}" class="text-indigo-600 hover:underline">Edit</a>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="6" class="px-3 py-6 text-center text-gray-500 dark:text-gray-300">No items.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="border-t dark:border-gray-800"></div>
  </form>

  {{-- Pagination: Prev/Next a sinistra, numerazione a destra su desktop (già sopra) --}}
  @php
    $prev = $assets->previousPageUrl();
    $next = $assets->nextPageUrl();
  @endphp
  <div class="mt-4 flex items-center gap-2 sm:hidden">
    <a href="{{ $prev ?: '#' }}"
       class="inline-flex items-center gap-1 rounded-lg border px-3 py-1.5 text-sm
              {{ $prev ? 'hover:bg-gray-50 dark:hover:bg-gray-800 dark:border-gray-800' : 'pointer-events-none opacity-50 dark:border-gray-800' }}">
      ‹ Prev
    </a>
    <a href="{{ $next ?: '#' }}"
       class="inline-flex items-center gap-1 rounded-lg border px-3 py-1.5 text-sm
              {{ $next ? 'hover:bg-gray-50 dark:hover:bg-gray-800 dark:border-gray-800' : 'pointer-events-none opacity-50 dark:border-gray-800' }}">
      Next ›
    </a>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', () => {
      // Seleziona tutti
      const checkAll = document.getElementById('checkAll');
      const rows = () => Array.from(document.querySelectorAll('.rowcheck'));
      checkAll?.addEventListener('change', e => rows().forEach(c => c.checked = e.target.checked));

      // Toggle filtri su mobile
      const filtersToggle = document.getElementById('filtersToggle');
      const filtersBar    = document.getElementById('filtersBar');
      const mq            = window.matchMedia('(max-width: 639px)');

      function applyFiltersVisibility() {
        if (mq.matches) {
          filtersBar.dataset.visible = filtersBar.dataset.visible || '0';
          if (filtersBar.dataset.visible === '1') {
            filtersBar.classList.remove('hidden');
          } else {
            filtersBar.classList.add('hidden');
          }
        } else {
          filtersBar.classList.remove('hidden');
          filtersBar.dataset.visible = '1';
        }
      }

      filtersToggle?.addEventListener('click', () => {
        filtersBar.dataset.visible = filtersBar.dataset.visible === '1' ? '0' : '1';
        applyFiltersVisibility();
      });

      mq.addEventListener?.('change', applyFiltersVisibility);
      applyFiltersVisibility();
    });
  </script>
</x-admin-layout>