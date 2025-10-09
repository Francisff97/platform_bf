<x-admin-layout title="SEO Media">
  {{-- Search --}}
  <form method="GET" class="mb-4 flex items-center gap-2">
    <input name="search" value="{{ request('search') }}" placeholder="Search path…"
           class="h-10 w-full max-w-md rounded-xl border border-[color:var(--accent)]/40 px-3
                  dark:bg-gray-900 dark:text-white dark:border-gray-800" />
    <button class="h-10 rounded-xl bg-[var(--accent)] px-4 text-white hover:opacity-90">Search</button>
  </form>

  <form method="POST" action="{{ route('admin.seo.media.bulk') }}"
        class="rounded-2xl border bg-white/70 shadow-sm backdrop-blur dark:bg-gray-900/70 dark:border-gray-800 overflow-hidden">
    @csrf

    {{-- Toolbar (vale per mobile e desktop) --}}
    <div class="flex flex-wrap items-center gap-2 border-b p-3 dark:border-gray-800">
      <label class="inline-flex items-center gap-2 text-sm">
        <input id="checkAll" type="checkbox"
               class="h-4 w-4 rounded border-gray-300 dark:border-gray-700">
        <span>Select all</span>
      </label>

      <div class="ml-auto flex items-center gap-2">
        <input type="text" name="alt_text" placeholder="Set ALT for selected…"
               class="h-10 w-56 rounded-xl border border-[color:var(--accent)]/40 px-3
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
            <th class="px-3 py-2"><!-- lo switch è sopra nella toolbar --></th>
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

    {{-- Footer bulk actions (duplicato già nella toolbar, qui lasciamo solo bordo per chiusura) --}}
    <div class="border-t dark:border-gray-800"></div>
  </form>

  <div class="mt-4">{{ $assets->links() }}</div>

  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const checkAll = document.getElementById('checkAll');
      const rows = () => Array.from(document.querySelectorAll('.rowcheck'));
      checkAll?.addEventListener('change', e => rows().forEach(c => c.checked = e.target.checked));
    });
  </script>
</x-admin-layout>