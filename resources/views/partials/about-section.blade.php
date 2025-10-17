{{-- resources/views/partials/about-section.blade.php --}}
@props(['s'])

@php
  // Dati immagine comuni
  $p   = $s->image_path ?? null;
  $alt = img_alt($s) ?: ($s->title ?? 'About image');

  // --- Varianti 16:9 (per layout "hero") ---
  $src16   = $p ? img_url($p, 1200, 675, 82, 'cover') : null; // default LCP-friendly
  $srcset16 = $p ? implode(', ', [
                img_url($p, 768,  432, 82, 'cover').' 768w',
                img_url($p, 1200, 675, 82, 'cover').' 1200w',
                img_url($p, 1920, 1080,82, 'cover').' 1920w',
              ]) : null;

  // --- Varianti 4:3 (per image_left / image_right) ---
  $src43   = $p ? img_url($p, 1200, 900, 82, 'cover') : null;
  $srcset43 = $p ? implode(', ', [
                img_url($p, 768,  576, 82, 'cover').' 768w',
                img_url($p, 1200, 900, 82, 'cover').' 1200w',
                img_url($p, 1600, 1200,82,'cover').' 1600w',
              ]) : null;
@endphp

@if($s->layout === 'hero')
  <section class="rounded-2xl my-[50px] border p-8 bg-gradient-to-br from-indigo-50 to-white dark:from-gray-800 dark:to-gray-900">
    @if($s->title)
      <h2 class="text-2xl font-bold mb-2">{{ $s->title }}</h2>
    @endif

    @if($s->body)
      <p class="text-gray-600 dark:text-gray-300 leading-relaxed">{{ $s->body }}</p>
    @endif

    @if($src16)
      <img
        src="{{ $src16 }}"
        @if($srcset16) srcset="{{ $srcset16 }}" sizes="100vw" @endif
        alt="{{ $alt }}"
        class="mt-6 rounded-xl w-full object-cover"
        width="1200" height="675"
        loading="lazy" decoding="async">
    @endif
  </section>

@elseif($s->layout === 'image_left' || $s->layout === 'image_right')
  <section class="grid items-center gap-6 md:grid-cols-2">
    {{-- immagine a sinistra --}}
    @if($s->layout === 'image_left' && $src43)
      <img
        src="{{ $src43 }}"
        @if($srcset43) srcset="{{ $srcset43 }}" sizes="(min-width: 768px) 50vw, 100vw" @endif
        alt="{{ $alt }}"
        class="rounded-xl w-full object-cover aspect-[4/3]"
        width="1200" height="900"
        loading="lazy" decoding="async">
    @endif

    {{-- testo --}}
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
        @if($srcset43) srcset="{{ $srcset43 }}" sizes="(min-width: 768px) 50vw, 100vw" @endif
        alt="{{ $alt }}"
        class="rounded-xl w-full object-cover aspect-[4/3]"
        width="1200" height="900"
        loading="lazy" decoding="async">
    @endif
  </section>

@else
  {{-- Solo testo --}}
  <section>
    @if($s->title)
      <h3 class="text-xl font-semibold mb-2">{{ $s->title }}</h3>
    @endif
    @if($s->body)
      <div class="prose max-w-none dark:prose-invert">{!! nl2br(e($s->body)) !!}</div>
    @endif
  </section>
@endif