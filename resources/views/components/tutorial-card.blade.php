@props(['tutorial'])


@php
  $embed = \App\Support\VideoEmbed::from($tutorial->video_url);
@endphp

<div class="mb-4 rounded-xl border p-4 dark:border-gray-800">
  <div class="mb-2 flex items-center justify-between">
    <div class="font-medium">{{ $tutorial->title }}</div>
    <span class="text-xs rounded px-2 py-0.5 {{ $tutorial->is_public ? 'bg-green-100 text-green-700' : 'bg-indigo-100 text-indigo-700' }}">
      {{ $tutorial->is_public ? 'Public' : 'Buyers' }}
    </span>
  </div>

  @if($embed)
    <div class="aspect-video w-full overflow-hidden rounded-lg">
      <iframe src="{{ $embed }}" class="h-full w-full" frameborder="0" allowfullscreen></iframe>
    </div>
  @else
    <a href="{{ $tutorial->video_url }}" target="_blank" class="text-indigo-600 hover:underline text-sm">
      Open video
    </a>
  @endif
</div>