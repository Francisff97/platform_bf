@props([
  'hero' => null,
  'title' => null,
  'subtitle' => null,
  'image' => null,          // URL assoluto o Storage::url(...)
  'ctaLabel' => null,
  'ctaUrl' => null,
  'height' => '60vh',
  'fullBleed' => true,
])

@php
  $h  = $hero->height_css ?? $height ?? '60vh';
  $fb = isset($hero) ? (bool) $hero->full_bleed : (bool) $fullBleed;
  $full = $fb ? 'full-bleed' : '';

  $src = $hero? Storage::url($hero->image_path) : $image;
  // Deriva versioni moderne se presenti (stesso path con estensione diversa)
  $srcWebp = $src ? preg_replace('/\.(png|jpe?g)$/i', '.webp', $src) : null;
  $srcAvif = $src ? preg_replace('/\.(png|jpe?g|webp)$/i', '.avif', $src) : null;

  // breakpoint widths (adatta se usi Glide/Thumbor: ?w=..)
  $w = [480, 768, 1024, 1440, 1920];
  $srcset = $src
    ? collect($w)->map(fn($ww) => $src.(str_contains($src,'?') ? "&" : "?")."w={$ww} {$ww}w")->join(', ')
    : '';
  $sizes = '(max-width: 640px) 100vw, (max-width: 1024px) 100vw, 1200px';
@endphp

<style>
  .full-bleed{width:100vw;position:relative;left:50%;right:50%;margin-left:-50vw;margin-right:-50vw}
  .hero-controls .swiper-button-next,.hero-controls .swiper-button-prev{color:#fff}
  .hero-controls .swiper-pagination-bullet{background:rgba(255,255,255,.6);opacity:1}
  .hero-controls .swiper-pagination-bullet-active{background:#fff}
  @media (max-width:767px){.inizio{height:250px!important}}
</style>

<section class="{{ $full }}">
  <figure class="inizio relative w-full" style="height: {{ $h }};">
    {{-- Placeholder per evitare CLS --}}
    <div class="absolute inset-0 bg-gradient-to-r from-gray-200 to-gray-300 dark:from-gray-900 dark:to-gray-800"></div>

    @if($src)
      <picture class="absolute inset-0">
        @if($srcAvif)<source type="image/avif" srcset="{{ $srcAvif }}" sizes="{{ $sizes }}"/><!-- best -->>@endif
        @if($srcWebp)<source type="image/webp" srcset="{{ $srcWebp }}" sizes="{{ $sizes }}"/><!-- modern -->@endif
        <img
          src="{{ $src }}"
          srcset="{{ $srcset }}"
          sizes="{{ $sizes }}"
          alt="{{ $hero->title ?? $title ?? 'Hero' }}"
          class="absolute inset-0 h-full w-full object-cover"
          fetchpriority="high"
          loading="eager"
          decoding="async"
          width="1920" height="1080"
          style="aspect-ratio: 16/9; contain-intrinsic-size: 1200px 675px;"
        >
      </picture>
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