@props([
  'src',
  'alt'     => null,
  'class'   => '',
  'width'   => null,
  'height'  => null,
  'loading' => null,   // true/false oppure 'lazy'/'eager' (se vuoi forzare)
])

@php
  use Illuminate\Support\Str;

  // 🔍 Verifica se siamo su una pagina "show"
  $routeName = optional(request()->route())->getName();
  $isShowPage = $routeName && Str::is(['*.show', 'packs.show', 'coaches.show', 'services.show', 'builders.show', 'partners.show'], $routeName);

  // 🧠 Recupera attributi SEO
  $attrs = \App\Support\SeoManager::imgAttrsByUrl($src);

  // 🖼 ALT finale
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
@endphp

<img src="{{ $src }}"
     @if($altFinal !== '') alt="{{ $altFinal }}" @endif
     @if($finalWidth)  width="{{ $finalWidth }}"   @endif
     @if($finalHeight) height="{{ $finalHeight }}" @endif
     loading="{{ $finalLoading }}"
     @if($fetchPriority) fetchpriority="{{ $fetchPriority }}" @endif
     class="{{ $class }}">