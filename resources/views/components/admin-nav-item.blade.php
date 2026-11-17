@props([
  'label',
  'route',                 // es: 'admin.packs.index'
  'match' => [],           // es: ['admin.packs.*']
  'icon'  => null,         // opzionale: svg path string o blade include
])

@php
  $isActive = $match
    ? request()->routeIs(...$match)
    : request()->routeIs($route);

  $base = 'flex items-center gap-2 rounded-lg px-3 py-2 text-sm transition';
  $active = 'bg-indigo-50 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-100';
  $normal = 'text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-800';
@endphp

<a href="{{ route($route) }}"
   class="{{ $base }} {{ $isActive ? $active : $normal }}">
  @if($icon)
    {{-- se passi un path SVG (stringa) --}}
    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">{!! $icon !!}</svg>
  @endif
  <span>{{ $label }}</span>
</a>