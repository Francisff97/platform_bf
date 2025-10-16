{{-- resources/views/components/img.blade.php --}}
@props([
  'src',
  'alt'     => null,
  'class'   => '',
  'width'   => null,
  'height'  => null,
  'loading' => null,   // true/false oppure 'lazy'/'eager'
])

@php
  use Illuminate\Support\Str;
  use Illuminate\Support\Facades\Storage;

  // Route info per LCP (show page => eager/high)
  $routeName  = optional(request()->route())->getName();
  $isShowPage = $routeName && Str::is([
      '*.show','packs.show','coaches.show','services.show','builders.show','partners.show'
  ], $routeName);

  // URL ORIGINE ASSOLUTA (con dominio), accetta:
  // - Storage::url(...) già passato
  // - path "storage/..." o "/storage/..."
  // - http/https
  $originUrl = $src;
  if (!Str::startsWith($originUrl, ['http://','https://'])) {
      $originUrl = Str::startsWith($originUrl, '/')
        ? url($originUrl)
        : Storage::disk('public')->url($originUrl);
  }

  // Path senza dominio per Cloudflare
  $originPath = parse_url($originUrl, PHP_URL_PATH) ?: '';

  // Parametri CF (dimensioni fisse come richiesto)
  $quality = 82;
  $fit     = 'cover';

  $breakpoints = [768, 1280, 1920];   // mobile / tablet / desktop grande
  $makeCf = function(int $w) use ($quality,$fit,$originPath){
      // NB: CF vuole un path relativo alla stessa origin dietro al proxy
      return "/cdn-cgi/image/width={$w},format=auto,quality={$quality},fit={$fit}{$originPath}";
  };

  // src default = 1280
  $cfSrc    = $makeCf(1280);
  $cfSrcset = collect($breakpoints)->map(fn($w) => "{$makeCf($w)} {$w}w")->implode(', ');

  // ALT
  $altFinal = $alt ?? '';

  // Loading
  if ($loading === 'lazy' || $loading === 'eager') {
      $finalLoading = $loading;
  } elseif (is_bool($loading)) {
      $finalLoading = $loading ? 'lazy' : 'eager';
  } else {
      $finalLoading = $isShowPage ? 'eager' : 'lazy';
  }

  // Fetchpriority
  $fetchPriority = $isShowPage ? 'high' : null;

  // Dimensioni (solo se passate o su show)
  $finalWidth  = $width  ?? ($isShowPage ? 1200 : null);
  $finalHeight = $height ?? ($isShowPage ? 630  : null);

  // Flag per usare CF: .env USE_CF_IMAGE=true (default true)
  $useCf = (bool) (config('cdn.use_cloudflare', env('USE_CF_IMAGE', true)));
@endphp

<img
  src="{{ $useCf ? $cfSrc : $originUrl }}"
  @if($useCf) srcset="{{ $cfSrcset }}" sizes="(min-width:1024px) 1280px, 100vw" @endif
  @if($altFinal !== '') alt="{{ $altFinal }}" @endif
  @if($finalWidth)  width="{{ $finalWidth }}"   @endif
  @if($finalHeight) height="{{ $finalHeight }}" @endif
  loading="{{ $finalLoading }}"
  @if($fetchPriority) fetchpriority="{{ $fetchPriority }}" @endif
  class="{{ $class }}"
  {{-- FALLBACK: se CF restituisce 400/errore passa all’origine e rimuovi srcset --}}
  onerror="this.onerror=null; this.src='{{ $originUrl }}'; this.removeAttribute('srcset'); this.removeAttribute('sizes');"
/>