@props([
  'hero'      => null,
  'title'     => null,
  'subtitle'  => null,
  'image'     => null,   // src alternativo opzionale
  'ctaLabel'  => null,
  'ctaUrl'    => null,
  'height'    => '60vh',
  'fullBleed' => true,
])

@php
  use Illuminate\Support\Facades\Storage;

  // Altezza & full-bleed (come avevi)
  $h   = $hero->height_css ?? $height ?? '60vh';
  $fb  = isset($hero) ? (bool)$hero->full_bleed : (bool)$fullBleed;
  $full = $fb ? 'full-bleed' : '';

  // Sorgente di base:
  // - se è presente $hero, uso il suo path originale su storage
  // - in alternativa uso l'eventuale $image passato al componente
  $origin = $hero?->image_path ? Storage::url($hero->image_path) : $image;

  // Se non c'è nulla, saltiamo la costruzione URL
  $src = null; $srcset = null; $sizes = null;

  if ($origin) {
      // Cloudflare Image Resizing con width fissi
      // (formato/qualità/fit coerenti con i pack)
      $path = ltrim(parse_url($origin, PHP_URL_PATH), '/');
      $quality = 82;
      $fit = 'cover';
      $widths = [768, 1200, 1920]; // mobile, tablet, desktop large

      // URL helper
      $cf = fn(int $w) => "/cdn-cgi/image/format=auto,quality={$quality},fit={$fit},width={$w}/{$path}";

      // Default src: 1200 (buon compromesso per LCP)
      $src    = $cf(1200);
      $srcset = collect($widths)->map(fn($w) => $cf($w)." {$w}w")->implode(', ');
      // Full-bleed → usa tutto lo spazio viewport
      $sizes  = '100vw';
  }
@endphp

<style>
  .full-bleed{width:100vw;position:relative;left:50%;right:50%;margin-left:-50vw;margin-right:-50vw}
  @media (max-width:767px){.inizio{height:250px!important}}
</style>

<section class="{{ $full }}">
  <figure class="inizio relative w-full" style="height: {{ $h }};">
    @php $heroImg = isset($hero) ? ($hero->image_url ?? null) : ($image ?? null); @endphp

@if($heroImg)
  <x-img :src="$heroImg"
         :alt="$hero->title ?? $title ?? 'Hero'"
         class="absolute inset-0 h-full w-full object-cover"
         :width="1920"
         :height="1080"
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