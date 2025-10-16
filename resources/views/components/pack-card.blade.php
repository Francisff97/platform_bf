{{-- resources/views/components/pack-card.blade.php --}}
@props([
  'pack',
  'ratio' => 'aspect-[4/3]', // rapporto pannello immagine
])

@php
  // usa accessor modello (CF /cdn-cgi/image o fallback webp)
  $img = $pack->image_url; 

  // testo alternativo: title > excerpt > nome categoria > fallback generico
  $altText = trim(strip_tags(
      $pack->title
      ?? $pack->excerpt
      ?? optional($pack->category)->name
      ?? 'Pack image'
  ));

  // Badge categoria
  $badgeClass = 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-100';
  $badgeStyle = '';

  $cat   = optional($pack->category);
  $color = $cat->color ?? null;
  $hex   = $cat->badge_color ?? null;

  if ($hex && \Illuminate\Support\Str::startsWith($hex, '#')) {
      $badgeClass = '';
      $badgeStyle = "background: {$hex}; color:#111; padding:2px 8px; border-radius:9999px; font-size:12px; font-weight:600;";
  } elseif ($color) {
      $map = [
        'indigo'  => 'bg-indigo-50 text-indigo-700 dark:bg-indigo-900 dark:text-indigo-100',
        'emerald' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900 dark:text-emerald-100',
        'rose'    => 'bg-rose-50 text-rose-700 dark:bg-rose-900 dark:text-rose-100',
        'amber'   => 'bg-amber-50 text-amber-700 dark:bg-amber-900 dark:text-amber-100',
        'sky'     => 'bg-sky-50 text-sky-700 dark:bg-sky-900 dark:text-sky-100',
        'slate'   => 'bg-slate-50 text-slate-700 dark:bg-slate-900 dark:text-slate-100',
        'violet'  => 'bg-violet-50 text-violet-700 dark:bg-violet-900 dark:text-violet-100',
        'cyan'    => 'bg-cyan-50 text-cyan-700 dark:bg-cyan-900 dark:text-cyan-100',
        'pink'    => 'bg-pink-50 text-pink-700 dark:bg-pink-900 dark:text-pink-100',
        'lime'    => 'bg-lime-50 text-lime-700 dark:bg-lime-900 dark:text-lime-100',
        'teal'    => 'bg-teal-50 text-teal-700 dark:bg-teal-900 dark:text-teal-100',
      ];
      $badgeClass = $map[$color] ?? $badgeClass;
  }
@endphp

<a href="{{ route('packs.show', $pack->slug) }}"
   class="group relative block overflow-hidden rounded-3xl shadow-sm ring-1 ring-black/5 transition hover:shadow-md">

  {{-- Pannello immagine --}}
  <div class="relative {{ $ratio }} w-full bg-gray-200 dark:bg-gray-800">
    @if($img)
      <img
        src="{{ $img }}"
        alt="{{ $altText }}"
        class="absolute inset-0 h-full w-full object-cover"
        loading="lazy"
        decoding="async"
        fetchpriority="low"
        referrerpolicy="no-referrer"
      >
    @else
      <div class="absolute inset-0 grid place-items-center text-xs text-gray-500">No image</div>
    @endif

    {{-- Overlay + contenuti --}}
    <div class="absolute inset-0 flex flex-col justify-between p-4
                bg-gradient-to-b from-black/10 via-black/0 to-black/40
                dark:from-black/20 dark:via-black/0 dark:to-black/60">

      {{-- Badge categoria --}}
      @if($cat && $cat->name)
        <span class="inline-flex w-fit items-center rounded-full px-2.5 py-0.5 text-[11px] font-semibold {{ $badgeClass }}"
              @if($badgeStyle) style="{{ $badgeStyle }}" @endif>
          {{ $cat->name }}
        </span>
      @endif

      {{-- Footer testo su immagine --}}
      <div class="flex items-end justify-between gap-3">
        <div class="min-w-0">
          <div class="line-clamp-2 font-semibold text-white drop-shadow-sm">
            {{ $pack->title }}
          </div>
          @if($pack->excerpt)
            <div class="mt-0.5 line-clamp-2 text-xs text-white/90">
              {{ $pack->excerpt }}
            </div>
          @endif
        </div>

        <div class="shrink-0 text-right">
          <div class="font-orbitron text-base font-extrabold text-white drop-shadow">
            @money($pack->price_cents, $pack->currency)
          </div>
          @if($pack->builder)
            <div class="mt-0.5 text-[11px] text-white/85">by {{ $pack->builder->name }}</div>
          @endif
        </div>
      </div>
    </div>
  </div>
</a>