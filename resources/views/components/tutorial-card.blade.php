@props(['tutorial'])

@php
  /** @var \App\Models\Tutorial $tutorial */
  $embed = $tutorial->embed_url; // accessor
@endphp

<div class="rounded-xl border border-gray-200 p-3 dark:border-gray-800">
  <div class="text-sm font-medium mb-2">{{ $tutorial->title ?: 'Tutorial' }}</div>

  @if($embed)
    <div class="aspect-video w-full overflow-hidden rounded-lg ring-1 ring-black/5 dark:ring-white/10">
      <iframe
        src="{{ $embed }}"
        class="h-full w-full"
        frameborder="0"
        allowfullscreen
        loading="lazy"></iframe>
    </div>
  @else
    <div class="text-xs text-gray-500">Video not available.</div>
  @endif

  <div class="mt-2 text-[11px] uppercase text-gray-500">
    {{ $tutorial->is_public ? 'Public' : 'Private' }}
  </div>
</div>
