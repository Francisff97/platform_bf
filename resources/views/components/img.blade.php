@props([
  'src',
  'alt'     => null,
  'class'   => '',
  'width'   => null,
  'height'  => null,
  'loading' => null,   // true/false oppure 'lazy'/'eager' (se vuoi forzare)
])

@php
  // Prendi i valori dal catalogo SEO per questa URL (alt, lazy di default)
  $attrs = \App\Support\SeoManager::imgAttrsByUrl($src);

  // ALT: se passo un alt esplicito lo uso, altrimenti quello del catalogo, altrimenti stringa vuota
  $altFinal = (isset($alt) && $alt !== null && $alt !== '')
    ? $alt
    : (isset($attrs['alt']) ? $attrs['alt'] : '');

  // LAZY: se passo loading esplicito lo rispetto, altrimenti fallback al catalogo (default: true)
  if ($loading === 'lazy' || $loading === 'eager') {
    $lazyFlag = ($loading === 'lazy');
  } elseif ($loading === true || $loading === false) {
    $lazyFlag = (bool) $loading;
  } else {
    $lazyFlag = isset($attrs['lazy']) ? (bool)$attrs['lazy'] : true;
  }
@endphp

<img src="{{ $src }}"
     @if($altFinal !== '') alt="{{ $altFinal }}" @endif
     @if(!empty($width))  width="{{ $width }}"   @endif
     @if(!empty($height)) height="{{ $height }}"  @endif
     loading="{{ $lazyFlag ? 'lazy' : 'eager' }}"
     fetchorigin="high"
     class="{{ $class }}">
