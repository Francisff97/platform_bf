@php
  // Path grezzo dal DB
  $path = $s->image_path ?? null;

  // ALT centralizzato (SEO -> Media), fallback a titolo o stringa generica
  $alt  = img_alt($s) ?: ($s->title ?? 'About image');

  // Preset dimensioni:
  // - hero: 16:9 grande
  // - blocchi left/right: circa 4:3
  $srcHero = $path ? img_url($path, 1920, 1080) : null;
  $src43   = $path ? img_url($path, 1200, 900)  : null;
@endphp

@if($s->layout === 'hero')
  <section class="rounded-2xl my-[50px] bg-gradient-to-br from-indigo-50 to-white dark:from-gray-800 dark:to-gray-900 border p-8">
    @if($s->title)
      <h2 class="text-2xl font-bold mb-2">{{ $s->title }}</h2>
    @endif
    @if($s->body)
      <p class="text-gray-600 dark:text-gray-300 leading-relaxed">{{ $s->body }}</p>
    @endif

    @if($srcHero)
      <img
        src="{{ $srcHero }}"
        alt="{{ $alt }}"
        class="mt-6 rounded-xl w-full object-cover"
        width="1920" height="1080"
        loading="eager" fetchpriority="high" decoding="async">
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