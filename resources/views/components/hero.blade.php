@props([
  'hero'      => null,
  'title'     => null,
  'subtitle'  => null,
  'image'     => null,   // path opzionale (storage path tipo "slides/foo.jpg")
  'ctaLabel'  => null,
  'ctaUrl'    => null,
  'height'    => '60vh',
  'fullBleed' => true,
])

@php
  use App\Support\Img;
  use Illuminate\Support\Facades\Storage;

  // Altezza & full-bleed
  $h    = $hero->height_css ?? $height ?? '60vh';
  $fb   = isset($hero) ? (bool)$hero->full_bleed : (bool)$fullBleed;
  $full = $fb ? 'full-bleed' : '';

  // Path immagine (preferisci sempre il path su storage)
  $path = $hero->image_path ?? $image ?? null;

  // Src ottimizzato (CF) + origin fallback
  $src    = $path ? img_url($path, 1920, 1080, 82, 'cover') : null;
  $origin = $path ? Img::origin($path) : null;

  // ALT centralizzato (SEO -> Media) con fallback al titolo
  $alt = img_alt($hero ?? null) ?: ($hero->title ?? $title ?? 'Hero');
@endphp

<style>
  .full-bleed{width:100vw;position:relative;left:50%;right:50%;margin-left:-50vw;margin-right:-50vw}
  @media (max-width:767px){.inizio{height:250px!important}}
</style>

<section class="{{ $full }}">
  <figure class="inizio relative w-full" style="height: {{ $h }};">
    @if($origin)
      <x-img
        :src="$src"
        :origin="$origin"
        :alt="$alt"
        width="1920"
        height="1080"
        class="absolute inset-0 h-full w-full object-cover"
        loading="eager"
      />
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