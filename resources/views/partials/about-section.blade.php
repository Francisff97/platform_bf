@php
  $p = $s->image_path ?? null;
  $srcset = $p ? implode(', ', [
      img_url($p, 768,  432, 82, 'cover').' 768w',
      img_url($p, 1200, 675, 82, 'cover').' 1200w',
      img_url($p, 1920, 1080,82, 'cover').' 1920w',
  ]) : null;
  // src “di default” = 1200w per avere LCP veloce e peso ok
  $src = $p ? img_url($p, 1200, 675, 82, 'cover') : null;
  $alt = img_alt($s) ?: ($s->title ?? 'Slide');
@endphp

@if($src)
  <img
    src="{{ $src }}"
    @if($srcset) srcset="{{ $srcset }}" sizes="100vw" @endif
    alt="{{ $alt }}"
    width="1200" height="675"
    class="absolute inset-0 h-full w-full object-cover"
    loading="{{ $loop->first ? 'eager' : 'lazy' }}"
    {{ $loop->first ? 'fetchpriority=high importance=high' : '' }}
    decoding="async">
@endif
  </section>

@elseif($s->layout === 'image_left' || $s->layout === 'image_right')
  <section class="grid items-center gap-6 md:grid-cols-2">
    {{-- immagine a sinistra --}}
    @if($s->layout === 'image_left' && $src43)
      <img
        src="{{ $src43 }}"
        alt="{{ $alt }}"
        class="rounded-xl w-full object-cover aspect-[4/3]"
        width="1200" height="900"
        loading="lazy" decoding="async">
    @endif

    <div>
      @if($s->title)
        <h3 class="text-xl font-semibold mb-2">{{ $s->title }}</h3>
      @endif
      @if($s->body)
        <div class="prose max-w-none dark:prose-invert">{!! nl2br(e($s->body)) !!}</div>
      @endif
    </div>

    {{-- immagine a destra --}}
    @if($s->layout === 'image_right' && $src43)
      <img
        src="{{ $src43 }}"
        alt="{{ $alt }}"
        class="rounded-xl w-full object-cover aspect-[4/3]"
        width="1200" height="900"
        loading="lazy" decoding="async">
    @endif
  </section>

@else
  <section>
    @if($s->title)
      <h3 class="text-xl font-semibold mb-2">{{ $s->title }}</h3>
    @endif
    @if($s->body)
      <div class="prose max-w-none dark:prose-invert">{!! nl2br(e($s->body)) !!}</div>
    @endif
  </section>
@endif