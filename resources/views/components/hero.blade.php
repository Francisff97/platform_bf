@props([
  'hero' => null,
  'title' => null,
  'subtitle' => null,
  'image' => null,          // absolute URL or Storage::url(...)
  'ctaLabel' => null,
  'ctaUrl' => null,
  'height' => '60vh',
  'fullBleed' => true,
])

@php
  // height & full-bleed
  $h  = optional($hero)->height_css ?? $height ?? '60vh';
  $fb = isset($hero) ? (bool) optional($hero)->full_bleed : (bool) $fullBleed;
  $full = $fb ? 'full-bleed' : '';

  // robust source resolution
  $src = null;
  if (isset($hero) && !empty($hero->image_path)) {
      // ✅ controlla se il file esiste nello storage pubblico o già in /public
      $path = $hero->image_path;
      if (Str::startsWith($path, ['http://', 'https://', '/'])) {
          $src = asset(ltrim($path, '/'));
      } elseif (Storage::disk('public')->exists($path)) {
          $src = Storage::url($path);
      } else {
          $src = asset('storage/'.$path);
      }
  } elseif (!empty($image)) {
      $src = $image;
  }

  // optional modern formats (only swap extension, don't break if missing)
  $srcWebp = $src ? preg_replace('/\.(png|jpe?g)$/i', '.webp', $src) : null;
  $srcAvif = $src ? preg_replace('/\.(png|jpe?g|webp)$/i', '.avif', $src) : null;

  $widths = [480, 768, 1024, 1440, 1920];
  $maxW   = max($widths);
  $srcset = $src
    ? collect($widths)
        ->map(fn($w) => $src.(str_contains($src,'?') ? "&" : "?")."w={$w} {$w}w")
        ->join(', ')
    : '';
  $sizes = '(max-width: 640px) 100vw, (max-width: 1024px) 100vw, 1200px';
@endphp
@if(isset($hero) && !empty($hero->image_path))
  <div style="background: #111; color: #0f0; font-size: 12px; padding:4px">
    Hero path: {{ $hero->image_path }}<br>
    Storage::exists? {{ Storage::disk('public')->exists($hero->image_path) ? '✅ yes' : '❌ no' }}<br>
    URL: {{ Storage::url($hero->image_path) }}
  </div>
@endif
<style>
  .full-bleed{width:100vw;position:relative;left:50%;right:50%;margin-left:-50vw;margin-right:-50vw}
  .hero-controls .swiper-button-next,.hero-controls .swiper-button-prev{color:#fff}
  .hero-controls .swiper-pagination-bullet{background:rgba(255,255,255,.6);opacity:1}
  .hero-controls .swiper-pagination-bullet-active{background:#fff}
  @media (max-width:767px){.inizio{height:250px!important}}
</style>

<section class="{{ $full }}">
  <figure class="inizio relative w-full" style="height: {{ $h }};">
    {{-- base placeholder to avoid CLS --}}
    <div class="absolute inset-0 bg-gradient-to-r from-gray-200 to-gray-300 dark:from-gray-900 dark:to-gray-800"></div>

    @if($src)
      <picture class="absolute inset-0">
        @if($srcAvif)
          <source type="image/avif" srcset="{{ $srcAvif }}" sizes="{{ $sizes }}">
        @endif
        @if($srcWebp)
          <source type="image/webp" srcset="{{ $srcWebp }}" sizes="{{ $sizes }}">
        @endif>
        <img
          src="{{ $src }}"
          @if($srcset) srcset="{{ $srcset }}" sizes="{{ $sizes }}" @endif
          alt="{{ optional($hero)->title ?? $title ?? 'Hero' }}"
          class="absolute inset-0 block h-full w-full object-cover"
          fetchpriority="high"
          loading="eager"
          decoding="async"
          width="{{ $maxW }}"
          height="1080"   {{-- harmless hint; real size is controlled by CSS --}}
        >
      </picture>
    @endif

    <div class="absolute inset-0 bg-gradient-to-b from-black/55 via-black/35 to-black/60"></div>

    <figcaption class="relative z-10 mx-auto flex h-full max-w-[1200px] items-center px-4 sm:px-6">
      <div class="max-w-xl text-white">
        @if((isset($hero) && $hero->title) || $title)
          <h1 class="font-orbitron text-3xl sm:text-4xl md:text-5xl drop-shadow-[0_2px_8px_rgba(0,0,0,0.6)]">
            {{ optional($hero)->title ?? $title }}
          </h1>
        @endif
        @if((isset($hero) && $hero->subtitle) || $subtitle)
          <p class="mt-2 text-white/90">{{ optional($hero)->subtitle ?? $subtitle }}</p>
        @endif
        @if((isset($hero) && $hero->cta_url) || $ctaUrl)
          <a href="{{ optional($hero)->cta_url ?? $ctaUrl }}"
             class="mt-4 inline-flex items-center rounded-full px-4 py-2 text-white hover:opacity-90"
             style="background: var(--accent);">
            {{ optional($hero)->cta_label ?? $ctaLabel ?? 'Learn more' }}
          </a>
        @endif
      </div>
    </figcaption>
  </figure>
</section>