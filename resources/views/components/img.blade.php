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

  // 🔍 Sei in una pagina "show"?
  $routeName  = optional(request()->route())->getName();
  $isShowPage = $routeName && Str::is(
    ['*.show','packs.show','coaches.show','services.show','builders.show','partners.show'],
    $routeName
  );

  // 🧠 Attributi SEO (opzionali)
  $attrs    = \App\Support\SeoManager::imgAttrsByUrl($src);
  $altFinal = filled($alt) ? $alt : ($attrs['alt'] ?? '');

  // 💤 Loading
  if ($loading === 'lazy' || $loading === 'eager') {
      $finalLoading = $loading;
  } elseif (is_bool($loading)) {
      $finalLoading = $loading ? 'lazy' : 'eager';
  } else {
      $finalLoading = $isShowPage ? 'eager' : (($attrs['lazy'] ?? true) ? 'lazy' : 'eager');
  }

  // ⚡ Fetchpriority
  $fetchPriority = $isShowPage ? 'high' : null;

  // 📐 Dimensioni (solo per show se non passate)
  $finalWidth  = $width  ?? ($isShowPage ? 1200 : null);
  $finalHeight = $height ?? ($isShowPage ? 630  : null);

  // =========================
  //  Cloudflare Image URL
  // =========================
  $cfEnabled = (bool) (config('cdn.use_cloudflare', env('USE_CF_IMAGE', true)));

  /**
   * Costruisce URL CF: /cdn-cgi/image/{ops}/<path>
   * - Se width/height presenti: li fissa (p.es. 1200x630 hero)
   * - Altrimenti usa width=auto,dpr=auto per i Client Hints
   * - format=auto, quality da config (default 82), fit=cover
   */
  $buildCfUrl = function (string $url, ?int $w, ?int $h) {
      // se è già un URL CF o esterno non trasformabile → ritorna com’è
      if (Str::startsWith($url, ['/cdn-cgi/image/', 'http://', 'https://']) && !Str::startsWith($url, url('/'))) {
          return $url;
      }

      // Se l'URL è assoluto del nostro dominio, estrai solo il path
      $parsed = parse_url($url);
      $path   = $parsed['path'] ?? $url;
      if (!Str::startsWith($path, '/')) $path = '/'.$path;

      // Non trasformare data: URI o SVG inline
      if (Str::startsWith($path, ['data:', 'blob:'])) {
          return $url;
      }

      $q   = (int) config('cdn.quality', 82);
      $fit = (string) config('cdn.fit', 'cover');

      $ops = ['format=auto', "quality={$q}", "fit={$fit}"];
      if ($w) { $ops[] = "width={$w}"; } else { $ops[] = 'width=auto'; }
      if ($h) { $ops[] = "height={$h}"; }
      $ops[] = 'dpr=auto';

      return '/cdn-cgi/image/'.implode(',', $ops).$path;
  };

  // Calcolo SRC finale
  $srcFinal = $src;

  if ($cfEnabled && $src) {
      $srcFinal = $buildCfUrl($src, $finalWidth, $finalHeight);
  }
@endphp

<img
  src="{{ $srcFinal }}"
  @if($altFinal !== '') alt="{{ $altFinal }}" @endif
  @if($finalWidth)  width="{{ $finalWidth }}"   @endif
  @if($finalHeight) height="{{ $finalHeight }}" @endif
  loading="{{ $finalLoading }}"
  decoding="async"
  @if($fetchPriority) fetchpriority="{{ $fetchPriority }}" @endif
  class="{{ $class }}"
>