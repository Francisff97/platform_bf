{{-- resources/views/components/addon-card.blade.php --}}
@props(['addon'])

@php
  $price = number_format($addon['price_eur'] ?? 0, 0, ',', '.').'€';
@endphp

<div class="rounded-2xl border bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
  <div class="aspect-[4/3] w-full overflow-hidden rounded-xl bg-gray-100 dark:bg-gray-800">
    @if(!empty($addon['image']))
      <img src="{{ $addon['image'] }}" alt="" class="h-full w-full object-cover">
    @endif
  </div>

  <div class="mt-3 flex items-start justify-between gap-3">
    <div>
      <div class="font-semibold">{{ $addon['title'] }}</div>
      <div class="mt-0.5 text-xs text-gray-600 dark:text-gray-400">
        {{ $addon['subtitle'] ?? '' }}
      </div>
    </div>
    <div class="shrink-0 font-orbitron text-lg font-extrabold">{{ $price }}</div>
  </div>

  <a href="{{ $addon['cta_url'] ?? '#' }}"
     class="mt-3 inline-flex w-full items-center justify-center gap-2 rounded-xl bg-[var(--accent)] px-3 py-2 text-sm font-semibold text-white hover:opacity-90">
     {{ $addon['cta_label'] ?? 'Get the add-on' }}
  </a>
</div>