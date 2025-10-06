@props([
  'hero' => null,
  // fallback se non passi un modello
  'title' => null,
  'subtitle' => null,
  'image' => null,
  'ctaLabel' => null,
  'ctaUrl' => null,
  'height' => '60vh',
  'fullBleed' => true,
])

@php
  $h  = $hero->height_css ?? $height ?? '60vh';
  $fb = isset($hero) ? (bool)$hero->full_bleed : (bool)$fullBleed;

  // classe per sforare i 1200px
  $full = $fb ? 'full-bleed' : '';
@endphp

<style>
  .full-bleed{width:100vw;position:relative;left:50%;right:50%;margin-left:-50vw;margin-right:-50vw}
  .hero-controls .swiper-button-next, 
  .hero-controls .swiper-button-prev { color:#fff; }
  .hero-controls .swiper-pagination-bullet{ background:rgba(255,255,255,.6); opacity:1; }
  .hero-controls .swiper-pagination-bullet-active{ background:#fff; }
    @media screen and (max-width:767px){.inizio{height: 300px !important}}
</style>

<section class="{{ $full }}">
  <figure class="inizio relative w-full" style="height: {{ $h }};">
    @if(($hero && $hero->image_path) || $image)
      <img src="{{ $hero? Storage::url($hero->image_path) : $image }}"
           alt="{{ $hero->title ?? $title }}"
           class="absolute inset-0 h-full w-full object-cover">
    @else
      <div class="absolute inset-0 bg-gradient-to-r from-gray-200 to-gray-300 dark:from-gray-900 dark:to-gray-800"></div>
    @endif

    <div class="absolute inset-0 bg-gradient-to-b from-black/55 via-black/35 to-black/60"></div>

    <figcaption class="relative z-10 mx-auto flex h-full max-w-[1200px] items-center px-4 sm:px-6">
      <div class="max-w-xl text-white">
        @if(($hero && $hero->title) || $title)
          <h1 class="font-orbitron text-3xl sm:text-4xl md:text-5xl drop-shadow-[0_2px_8px_rgba(0,0,0,0.6)]">
            {{ $hero->title ?? $title }}
          </h1>
        @endif
        @if(($hero && $hero->subtitle) || $subtitle)
          <p class="mt-2 text-white/90">{{ $hero->subtitle ?? $subtitle }}</p>
        @endif
        @if(($hero && $hero->cta_url) || $ctaUrl)
          <a href="{{ $hero->cta_url ?? $ctaUrl }}"
             class="mt-4 inline-flex items-center rounded-full px-4 py-2 text-white hover:opacity-90"
             style="background: var(--accent);">
            {{ $hero->cta_label ?? $ctaLabel ?? 'Learn more' }}
          </a>
        @endif
      </div>
    </figcaption>
  </figure>
</section>
