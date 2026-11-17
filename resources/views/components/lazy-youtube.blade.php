@props([
  'videoId',          // obbligatorio (solo l'ID YouTube)
  'title' => 'Video', // opzionale
])

@php
  // thumbnail leggera (hqdefault è ~20–40KB)
  $thumb = "https://i.ytimg.com/vi/{$videoId}/hqdefault.jpg";
@endphp

<div class="youtube-facade relative aspect-video w-full overflow-hidden rounded-lg bg-black">
  <button type="button"
          class="group absolute inset-0 flex items-center justify-center"
          onclick="(function(btn){
            const w = btn.closest('.youtube-facade');
            const id = '{{ $videoId }}';
            const f = document.createElement('iframe');
            f.src = 'https://www.youtube.com/embed/' + id + '?autoplay=1&rel=0';
            f.title = @js($title);
            f.allow = 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share';
            f.setAttribute('allowfullscreen', '');
            f.className = 'absolute inset-0 h-full w-full border-0';
            w.innerHTML = '';
            w.appendChild(f);
          })(this)">
    <img src="{{ $thumb }}" alt="{{ $title }}" loading="lazy" decoding="async"
         class="absolute inset-0 h-full w-full object-cover opacity-90 transition-opacity group-hover:opacity-100">
    {{-- play icon --}}
    <svg class="relative z-10 h-16 w-16 text-white opacity-85 transition-opacity group-hover:opacity-100"
         aria-hidden="true" focusable="false" viewBox="0 0 68 48" fill="currentColor">
      <path d="M66.52 7.02A8 8 0 0 0 61.16 1.6C55.1 0 34 0 34 0S12.9 0 6.84 1.6A8 8 0 0 0 1.48 7.02C0 13.1 0 24 0 24s0 10.9 1.48 16.98A8 8 0 0 0 6.84 46.4C12.9 48 34 48 34 48s21.1 0 27.16-1.6a8 8 0 0 0 5.36-5.42C68 34.9 68 24 68 24s0-10.9-1.48-16.98zM27 34V14l18 10-18 10z"/>
    </svg>
  </button>
</div>