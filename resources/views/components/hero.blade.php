@props([
  'hero'      => null,
  'title'     => null,
  'subtitle'  => null,
  'image'     => null,   // opzionale: path storage o URL assoluto
  'ctaLabel'  => null,
  'ctaUrl'    => null,
  'height'    => '60vh',
  'fullBleed' => true,
])

@php
  // Altezza + full-bleed
  $h   = $hero->height_css ?? $height ?? '60vh';
  $fb  = isset($hero) ? (bool)$hero->full_bleed : (bool)$fullBleed;
  $full = $fb ? 'full-bleed' : '';

  // Path grezzo da DB (preferito per img_url); se non c’è, usa $image com’è
  $path = $hero->image_path ?? null;

  // Src ottimizzato (Cloudflare / WebP fallback) – MISMO URL usato anche per preload
  $src     = $path ? img_url($path, 1200, 675) : ($image ?: null);
  $srcset  = $path ? implode(', ', [
                img_url($path, 768, 432).' 768w',
                img_url($path, 1200, 675).' 1200w',
                img_url($path, 1920, 1080).' 1920w',
            ]) : null;
  $sizes   = '100vw';
  $altText = img_alt($hero) ?: ($hero->title ?? $title ?? 'Hero');
@endphp

<style>
  .full-bleed{width:100vw;position:relative;left:50%;right:50%;margin-left:-50vw;margin-right:-50vw}
  .inizio{overflow:hidden} /* evita scroll orizzontale */
  @media (max-width:767px){.inizio{height:250px!important}}
</style>

<section class="{{ $full }}">
  <figure class="inizio relative w-full" style="height: {{ $h }};">
    @if($src)
      {{-- Preload coerente con <img> (stesso URL) per LCP perfetto --}}
      <link rel="preload" as="image"
            href="{{ $src }}"
            @if($srcset) imagesrcset="{{ $srcset }}" imagesizes="{{ $sizes }}" @endif
            fetchpriority="high">

      <img
        src="{{ $src }}"
        @if($srcset) srcset="{{ $srcset }}" sizes="{{ $sizes }}" @endif
        alt="{{ $altText }}"
        width="1200" height="675"
        class="absolute inset-0 h-full w-full object-cover"
        loading="eager" fetchpriority="high" importance="high" decoding="async">
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