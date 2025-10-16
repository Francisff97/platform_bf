{{-- resources/views/components/img.blade.php --}}
@props([
  'src',
  'origin' => null,
  'alt'     => '',
  'class'   => '',
  'width'   => null,
  'height'  => null,
  'loading' => lazy,   // true/false oppure 'lazy'/'eager'
])

@php
  use Illuminate\Support\Str;

  // 🔍 Siamo in una pagina "show"?
  $routeName   = optional(request()->route())->getName();
  $isShowPage  = $routeName && Str::is(['*.show','packs.show','coaches.show','services.show','builders.show','partners.show'], $routeName);

  // 🧠 SEO attrs (se presenti nel tuo progetto)
  $attrs    = class_exists(\App\Support\SeoManager::class) ? \App\Support\SeoManager::imgAttrsByUrl($src) : [];
  $altFinal = filled($alt) ? $alt : ($attrs['alt'] ?? '');

  // 💤 Loading
  if ($loading === 'lazy' || $loading === 'eager') {
      $finalLoading = $loading;
  } elseif (is_bool($loading)) {
      $finalLoading = $loading ? 'lazy' : 'eager';
  } else {
      $finalLoading = $isShowPage ? 'eager' : (($attrs['lazy'] ?? true) ? 'lazy' : 'eager');
  }

  // ⚡ Fetchpriority (solo per la pagina show)
  $fetchPriority = $isShowPage ? 'high' : null;

  // 📐 Dimensioni HTML (solo per show se non passate esplicitamente)
  $finalWidth  = $width  ?? ($isShowPage ? 1200 : null);
  $finalHeight = $height ?? ($isShowPage ? 630  : null);

  // ⚙️ Config CDN
  $useCf  = (bool) config('cdn.use_cloudflare', false);
  $q      = (int)  config('cdn.quality', 82);
  $fit    = (string) config('cdn.fit', 'cover');
  $bps    = (array) config('cdn.breakpoints', [768,1280,1920]);
  sort($bps); // assicura ordine crescente

  // 🔗 Costruisci URL (CF o origin)
  $srcUrl   = $src;
  $srcset   = null;

  // ricava path relativo (serve a CF: /cdn-cgi/image/.../<path>)
  $path = $src ? ltrim(parse_url($src, PHP_URL_PATH) ?? $src, '/') : null;

  if ($useCf && $path) {
      // breakpoint fissi: 768 / 1280 / 1920 (configurabili)
      $mk = function(int $w, ?int $h = null) use ($fit, $q, $path) {
          $ops = ["width={$w}", "fit={$fit}", "quality={$q}", "format=auto"];
          if ($h) $ops[] = "height={$h}";
          return '/cdn-cgi/image/'.implode(',', $ops).'/'.$path;
      };

      // src: migliore per il contesto (show => 1920, altrimenti 1280 se disponibile)
      $bestW = $isShowPage ? max($bps) : ($bps[1] ?? max($bps));
      $srcUrl = $mk($bestW);

      // srcset fisso sugli stessi breakpoint
      $srcset = collect($bps)->map(fn($w) => $mk($w).' '.$w.'w')->implode(', ');
  }
@endphp

<img
  src="{{ $src }}"
  alt="{{ $alt }}"
  @if($width)  width="{{ $width }}"   @endif
  @if($height) height="{{ $height }}" @endif
  loading="{{ $loading }}" decoding="async"
  {{ $attributes->merge(['class' => $class]) }}
  @if($origin) onerror="this.onerror=null; this.src='{{ $origin }}'" @endif
>