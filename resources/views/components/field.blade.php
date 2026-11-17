@props([
  'label' => '',
  'value' => null,
  'copy'  => true,
])

<div class="rounded-xl border border-gray-200 bg-white/70 p-4 dark:border-gray-800 dark:bg-gray-900/60">
  @if($label !== '')
    <div class="mb-1 text-[11px] font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
      {{ $label }}
    </div>
  @endif

  <div class="flex items-start justify-between gap-3">
    <div class="min-w-0 text-sm text-gray-900 dark:text-gray-100">
      @if(trim($slot))
        {{-- Contenuto custom passato nello slot --}}
        {{ $slot }}
      @else
        {{-- Valore semplice --}}
        <span class="break-words">{{ $value ?? '—' }}</span>
      @endif
    </div>

    @if($copy)
      <button
        type="button"
        x-data
        @click="navigator.clipboard?.writeText(`{{ trim($slot) ? trim(preg_replace('/\s+/', ' ', $slot)) : ($value ?? '') }}`); $el.innerText='Copied'; setTimeout(()=> $el.innerText='Copy',1500)"
        class="shrink-0 rounded-lg border px-2.5 py-1 text-xs text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-gray-800">
        Copy
      </button>
    @endif
  </div>
</div>
