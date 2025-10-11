@props(['tutorial'])

@php
    use App\Support\VideoEmbed;

    $embedUrl = VideoEmbed::from($tutorial->video_url);
@endphp

<div class="overflow-hidden rounded-2xl border border-gray-200 bg-white/80 shadow-sm backdrop-blur
            dark:border-gray-800 dark:bg-gray-900/60">
    @if($embedUrl)
        <div class="aspect-video w-full overflow-hidden">
            <iframe src="{{ $embedUrl }}"
                    class="h-full w-full"
                    frameborder="0"
                    allowfullscreen
                    loading="lazy"></iframe>
        </div>
    @endif

    <div class="p-4">
        <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $tutorial->title }}</div>
        <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">
            {{ $tutorial->is_public ? 'Public tutorial' : 'Private tutorial' }}
        </div>
    </div>
</div>
