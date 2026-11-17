<x-app-layout>
  {{-- Header --}}
  <div class="mb-6 flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
    <div>
      <h1 class="text-2xl font-semibold tracking-tight text-gray-900 dark:text-gray-50">
        Map Columns: {{ ucfirst($entity) }}
      </h1>
      <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
        Map each CSV header to an entity field. Unmapped columns will be ignored.
      </p>
    </div>
    <div class="text-xs text-gray-500 dark:text-gray-400">
      File: <span class="font-medium text-gray-700 dark:text-gray-300">{{ basename($file) }}</span>
    </div>
  </div>

  <form action="{{ route('admin.csv.import') }}" method="POST" class="space-y-6">
    @csrf
    <input type="hidden" name="entity" value="{{ $entity }}">
    <input type="hidden" name="file" value="{{ $file }}">

    {{-- Mapping table card --}}
    <div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900/70">
      {{-- Table header tools --}}
      <div class="flex flex-col gap-3 p-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-center gap-2">
          <span class="inline-flex items-center gap-2 rounded-lg bg-gray-100 px-2.5 py-1.5 text-xs font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-300">
            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M3 6h18M3 12h18M3 18h18" stroke="currentColor" stroke-width="1.5" fill="none" stroke-linecap="round"/></svg>
            {{ count($headers) }} columns
          </span>
          <span class="hidden text-xs text-gray-500 sm:inline dark:text-gray-400">Example shown from the first valid row</span>
        </div>

        {{-- Quick filter headers --}}
        <div x-data="{q:''}" class="relative w-full sm:w-72">
          <input x-model="q" type="search" placeholder="Filter CSV columns..."
                 class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">
          <svg class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400 dark:text-gray-500" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M21 21l-4.35-4.35M10 18a8 8 0 100-16 8 8 0 000 16z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
          </svg>

          {{-- simple client filter using Alpine: hide rows that don't match --}}
          <template x-if="false"></template>
        </div>
      </div>

      <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead class="sticky top-0 z-10 bg-gray-50/90 backdrop-blur dark:bg-gray-900/80">
            <tr class="border-y border-gray-200 dark:border-gray-800">
              <th scope="col" class="p-3 text-left font-semibold text-gray-700 dark:text-gray-200">CSV Header</th>
              <th scope="col" class="p-3 text-left font-semibold text-gray-700 dark:text-gray-200">Map to Field</th>
              <th scope="col" class="p-3 text-left font-semibold text-gray-700 dark:text-gray-200">Example</th>
            </tr>
          </thead>
          <tbody x-data="{q:''}" x-init="$watch('$root.querySelector('input[type=search]')._x_model?.get(), v => q=v?.toLowerCase?.()||'')">
            @foreach($headers as $h)
              @php $example = $sample[0][$h] ?? '—'; @endphp
              <tr x-show="!q || '{{ Str::of($h)->lower() }}'.includes(q)"
                  class="border-b border-gray-100 hover:bg-gray-50/70 dark:border-gray-800 dark:hover:bg-gray-800/40">
                <td class="p-3 font-mono text-[13px] text-gray-800 dark:text-gray-100">{{ $h }}</td>
                <td class="p-3">
                  <div class="relative max-w-xs">
                    <select name="mapping[{{ $h }}]"
                            class="block w-full appearance-none rounded-lg border border-gray-300 bg-white px-3 py-2.5 pr-9 text-sm text-gray-900 shadow-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">
                      <option value="">— Do not map —</option>
                      @foreach($fields as $field => $meta)
                        <option value="{{ $field }}" @selected(($mapping[$h]??'') === $field)>
                          {{ $field }} — {{ $meta['label'] ?? $field }}
                        </option>
                      @endforeach
                    </select>
                    <span class="pointer-events-none absolute inset-y-0 right-3 flex items-center">
                      <svg class="h-4 w-4 text-gray-400 dark:text-gray-500" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.24a.75.75 0 01-1.06 0L5.21 8.29a.75.75 0 01.02-1.08z" clip-rule="evenodd"/></svg>
                    </span>
                  </div>
                </td>
                <td class="p-3 text-gray-600 dark:text-gray-300">
                  <span class="line-clamp-2 break-all">{{ $example }}</span>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>

    {{-- Run mode + sticky footer actions --}}
    <div class="h-16"></div> {{-- spacer for sticky footer --}}
    <div class="fixed inset-x-0 bottom-0 z-20 border-t border-gray-200 bg-white/85 backdrop-blur supports-[backdrop-filter]:bg-white/60 dark:border-gray-800 dark:bg-gray-900/80">
      <div class="mx-auto max-w-7xl px-4 py-3">
        <div class="flex flex-col items-stretch gap-3 sm:flex-row sm:items-center sm:justify-between">
          {{-- Mode toggle --}}
          <fieldset class="inline-flex overflow-hidden rounded-lg border border-gray-300 text-sm dark:border-gray-700" role="radiogroup" aria-label="Run mode">
            <label class="inline-flex items-center gap-2 px-3 py-2 text-gray-700 dark:text-gray-200">
              <input type="radio" name="mode" value="queue" class="accent-indigo-600 dark:accent-indigo-500" checked>
              Queue
            </label>
            <span class="h-full w-px bg-gray-200 dark:bg-gray-700"></span>
            <label class="inline-flex items-center gap-2 px-3 py-2 text-gray-700 dark:text-gray-200">
              <input type="radio" name="mode" value="sync" class="accent-indigo-600 dark:accent-indigo-500">
              Sync <span class="hidden sm:inline text-xs text-gray-500 dark:text-gray-400">(small files)</span>
            </label>
          </fieldset>

          <div class="flex items-center gap-3">
            <a href="{{ route('admin.csv.index') }}"
               class="inline-flex items-center justify-center rounded-lg border border-gray-300 px-3.5 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-gray-800">
              Cancel
            </a>
            <button type="submit"
                    class="inline-flex items-center justify-center gap-2 rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/40 dark:bg-indigo-500 dark:hover:bg-indigo-400">
              <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 4v10m0 0l-3.5-3.5M12 14l3.5-3.5M5 20h14" stroke="currentColor" stroke-width="1.5" fill="none" stroke-linecap="round" stroke-linejoin="round"/></svg>
              Start Import
            </button>
          </div>
        </div>
      </div>
    </div>

    {{-- Sample rows --}}
    <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900/70">
      <h2 class="mb-2 text-base font-semibold text-gray-900 dark:text-gray-50">Sample rows</h2>
      <div class="overflow-x-auto">
        <table class="min-w-full text-xs">
          <thead class="bg-gray-50 dark:bg-gray-900/60">
            <tr class="border-y border-gray-200 dark:border-gray-800">
              @foreach($headers as $h)
                <th class="p-2 text-left font-medium text-gray-700 dark:text-gray-200">{{ $h }}</th>
              @endforeach
            </tr>
          </thead>
          <tbody>
            @foreach($sample as $row)
              <tr class="border-b border-gray-100 hover:bg-gray-50/70 dark:border-gray-800 dark:hover:bg-gray-800/40">
                @foreach($headers as $h)
                  <td class="p-2 text-gray-700 dark:text-gray-300">{{ $row[$h] ?? '' }}</td>
                @endforeach
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </form>
</x-app-layout>