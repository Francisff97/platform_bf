{{-- resources/views/components/hero.blade.php --}}
@props([
  'hero'      => null,

  // fallback se non passi un modello
  'title'     => null,
  'subtitle'  => null,
  'image'     => null,
  'ctaLabel'  => null,
  'ctaUrl'    => null,

  // layout
  'height'    => '60vh',
  'fullBleed' => true,
])

@php
  // Dati sorgente (model o fallback props)
  $imgUrl  = $hero?->image_path ? Storage::url($hero->image_path) : $image;
  $altText = trim(($hero->title ?? $title) ?: 'Hero image');

  // Altezza e full-bleed
  $h  = $hero->height_css ?? $height ?? '60vh';
  $fb = isset($hero) ? (bool)$hero->full_bleed : (bool)$fullBleed;
  $fullCls = $fb ? 'full-bleed' : '';

  // sizes responsive
  $sizes = $fb ? '100vw' : '(max-width: 1280px) 100vw, 1200px';

  // opzionali width/height per ridurre CLS (se noti layout shift puoi aggiungerli nel model)
  $w = $hero->image_width  ?? null;
  $hImg = $hero->image_height ?? null;
@endphp

<style>
  .full-bleed {
    width: 100vw;
    position: relative;
    left: 50%;
    right: 50%;
    margin-left: -50vw;
    margin-right: -50vw;
  }

  .hero-controls .swiper-button-next, 
  .hero-controls .swiper-button-prev {
    color: #fff;
  }

  .hero-controls .swiper-pagination-bullet {
    background: rgba(255, 255, 255, .6);
    opacity: 1;
  }

  .hero-controls .swiper-pagination-bullet-active {
    background: #fff;
  }

  @media (max-width: 767px) {
    .hero-viewport { height: 250px !important; }
  }
</style>

<section class="{{ $fullCls }}">
  <figure class="relative w-full hero-viewport" style="height: {{ $h }};">
    @if($imgUrl)
      <img
        src="{{ $imgUrl }}"
        alt="{{ $altText }}"
        class="absolute inset-0 h-full w-full object-cover"
        loading="eager"
        fetchpriority="high"
        decoding="async"
        sizes="{{ $sizes }}"
        @if($w) width="{{ (int)$w }}" @endif
        @if($hImg) height="{{ (int)$hImg }}" @endif
      >
    @else
      <div class="absolute inset-0 bg-gradient-to-r from-gray-200 to-gray-300 dark:from-gray-900 dark:to-gray-800"></div>
    @endif

    {{-- overlay gradiente --}}
    <div class="absolute inset-0 bg-gradient-to-b from-black/55 via-black/35 to-black/60"></div>

    {{-- contenuto --}}
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