<?php
@props([
  'src',
  'alt' => null,
  'class' => '',
  'width' => null,
  'height' => null,
  'loading' => null,
])

@php
  $attrs = \App\Support\SeoManager::imgAttrsByUrl($src);
  $altFinal = $alt ?? ($attrs['alt'] ?? '');
  $lazy = $loading ?? (($attrs['lazy'] ?? true) ? 'lazy' : null);
@endphp

<img src="{{ $src }}"
     @if(!is_null($altFinal)) alt="{{ $altFinal }}" @endif
     @if($width) width="{{ $width }}" @endif
     @if($height) height="{{ $height }}" @endif
     @if($lazy) loading="{{ $lazy }}" @endif
     class="{{ $class }}">
