{{-- resources/views/home.blade.php --}}
<x-app-layout>
  {{-- ====== HERO FULL-BLEED ====== --}}
  <style>
    .full-bleed{width:100vw;position:relative;left:50%;right:50%;margin-left:-50vw;margin-right:-50vw}
    #homeHero, #homeHero .swiper, #homeHero .swiper-wrapper, #homeHero .swiper-slide { height: auto !important; }
    #homeHero .slide-figure{ height:70vh; min-height:480px; }
    @supports (height:70svh){ #homeHero .slide-figure{ height:70svh; } }
    #homeHero .swiper-button-next, #homeHero .swiper-button-prev { color:#fff; }
    #homeHero .swiper-pagination-bullet{ background:rgba(255,255,255,.6); opacity:1; }
    #homeHero .swiper-pagination-bullet-active{ background:#fff; }
    .card-ghost{ box-shadow: 0 8px 24px rgba(0,0,0,.08); }
    .ring-soft{ box-shadow: 0 1px 0 rgba(0,0,0,.04), inset 0 0 0 1px rgba(0,0,0,.06); }
  </style>

  <section class="full-bleed">
    <div id="homeHero" class="swiper w-full">
      <div class="swiper-wrapper">
        @foreach($slides as $s)
          <div class="swiper-slide">
            <figure class="slide-figure relative w-full">
              @if($s->image_path)
                <x-img :src="Storage::url($s->image_path)" :alt="$s->title" class="absolute inset-0 h-full w-full object-cover" />
              @else
                <div class="absolute inset-0 bg-gradient-to-r from-gray-200 to-gray-300 dark:from-gray-900 dark:to-gray-800"></div>
              @endif

              <div class="absolute inset-0 bg-gradient-to-b from-black/55 via-black/35 to-black/60"></div>

              <figcaption class="relative z-10 mx-auto flex h-full max-w-[1200px] items-center px-4 sm:px-6">
                <div class="max-w-xl">
                  @if($s->title)
                    <h2 class="font-orbitron text-3xl sm:text-4xl md:text-5xl text-white drop-shadow-[0_2px_8px_rgba(0,0,0,0.6)]">
                      {{ $s->title }}
                    </h2>
                  @endif
                  @if($s->subtitle)
                    <p class="mt-2 text-white/90 text-base sm:text-lg">{{ $s->subtitle }}</p>
                  @endif
                  @if($s->cta_url)
                    <a href="{{ $s->cta_url }}"
                       class="mt-5 inline-flex items-center gap-2 rounded-full px-5 py-2.5 text-white hover:opacity-90"
                       style="background:var(--accent)">
                      {{ $s->cta_label ?? 'Learn more' }}
                      <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                    </a>
                  @endif
                </div>
              </figcaption>
            </figure>
          </div>
        @endforeach
      </div>

      <div class="swiper-pagination !bottom-3"></div>
      <div class="swiper-button-prev"></div>
      <div class="swiper-button-next"></div>
    </div>
  </section>

  {{-- ====== PACKS ====== --}}
  <section class="mx-auto my-[70px] max-w-6xl px-4">
    <div class="mb-5 flex items-end justify-between gap-3">
      <div>
        <h2 class="font-orbitron text-2xl sm:text-3xl">Featured Packs</h2>
        <p class="text-sm text-gray-500 dark:text-gray-400">Curated builds ready to go.</p>
      </div>
      <a href="{{ route('packs.public') }}" class="hidden sm:inline-flex items-center gap-2 text-sm hover:opacity-80">
        Browse all
        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
      </a>
    </div>

    <div class="swiper" id="packsSwiper">
      <div class="swiper-wrapper">
        @if($latestPack)
          <div class="swiper-slide">
            <a href="{{ route('packs.show',$latestPack->slug) }}"
               class="group block overflow-hidden rounded-3xl bg-white ring-1 ring-black/5 transition hover:shadow-lg dark:bg-gray-900">
              @if($latestPack->image_path)
                <x-img :src="Storage::url($latestPack->image_path)" class="aspect-[16/9] w-full object-cover" />
              @endif
              <div class="p-4">
                <div class="flex items-center justify-between">
                  <h3 class="line-clamp-1 text-lg font-semibold">{{ $latestPack->title }}</h3>
                  <div class="font-orbitron text-[var(--accent)]">
                    @money($latestPack->price_cents, $latestPack->currency)
                  </div>
                </div>
                @if($latestPack->excerpt)
                  <p class="mt-1 line-clamp-2 text-sm text-gray-600 dark:text-gray-300">{{ $latestPack->excerpt }}</p>
                @endif
              </div>
            </a>
          </div>
        @endif

        @foreach(($otherPacks ?? collect()) as $p)
          <div class="swiper-slide">
            <x-pack-card :pack="$p" />
          </div>
        @endforeach
      </div>
      <div class="mt-3 flex items-center justify-end gap-2">
        <button id="packsPrev" class="rounded-full border px-3 py-1.5 dark:border-gray-700">Prev</button>
        <button id="packsNext" class="rounded-full border px-3 py-1.5 dark:border-gray-700">Next</button>
      </div>
    </div>
  </section>

  {{-- ====== SERVICES ====== --}}
  <section class="mx-auto my-[70px] max-w-6xl px-4">
    <div class="mb-5 flex items-end justify-between gap-3">
      <div>
        <h2 class="font-orbitron text-2xl sm:text-3xl">Our Services</h2>
        <p class="text-sm text-gray-500 dark:text-gray-400">Crafted to elevate your stack.</p>
      </div>
      <a href="{{ route('services.public') }}" class="hidden sm:inline-flex items-center gap-2 text-sm hover:opacity-80">
        Explore services
        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
      </a>
    </div>

    <div class="swiper" id="servicesSwiper">
      <div class="swiper-wrapper">
        @foreach($services as $s)
          <div class="swiper-slide">
            <div class="group overflow-hidden rounded-3xl bg-white ring-1 ring-black/5 transition hover:shadow-lg dark:bg-gray-900">
              @if($s->image_path)
                <x-img :src="Storage::url($s->image_path)" class="aspect-[16/9] w-full object-cover" />
              @endif
              <div class="p-4">
                <div class="flex items-center justify-between">
                  <h3 class="line-clamp-1 text-lg font-semibold">{{ $s->name ?? $s->title }}</h3>
                  @if(isset($s->price_cents))
                    <div class="font-orbitron text-[var(--accent)]">@money($s->price_cents, $s->currency ?? 'EUR')</div>
                  @endif
                </div>
                @if($s->excerpt)
                  <p class="mt-1 line-clamp-2 text-sm text-gray-600 dark:text-gray-300">{{ $s->excerpt }}</p>
                @endif
              </div>
            </div>
          </div>
        @endforeach
      </div>
      <div class="mt-3 flex items-center justify-end gap-2">
        <button id="servicesPrev" class="rounded-full border px-3 py-1.5 dark:border-gray-700">Prev</button>
        <button id="servicesNext" class="rounded-full border px-3 py-1.5 dark:border-gray-700">Next</button>
      </div>
    </div>
  </section>

  {{-- ====== BUILDERS ====== --}}
  <section class="mx-auto my-[70px] max-w-6xl px-4">
    <h3 class="mb-5 font-orbitron text-2xl sm:text-3xl">Our Builders</h3>
    <div class="swiper" id="buildersSwiper">
      <div class="swiper-wrapper">
        @foreach($builders as $b)
          <div class="swiper-slide">
            <a href="{{ route('builders.show',$b->slug) }}"
               class="block rounded-3xl bg-white p-4 text-center ring-1 ring-black/5 transition hover:shadow-lg dark:bg-gray-900">
              <div class="mx-auto mb-2 h-16 w-16 overflow-hidden rounded-full bg-gray-200 dark:bg-gray-800">
                @if($b->image_path)
                  <x-img :src="Storage::url($b->image_path)" class="h-full w-full object-cover" :alt="$b->name" />
                @endif
              </div>
              <div class="font-semibold line-clamp-1">{{ $b->name }}</div>
              <div class="text-xs text-gray-500 line-clamp-1">{{ $b->team ?? '—' }}</div>
            </a>
          </div>
        @endforeach
      </div>
      <div class="mt-3 flex items-center justify-end gap-2">
        <button id="buildersPrev" class="rounded-full border px-3 py-1.5 dark:border-gray-700">Prev</button>
        <button id="buildersNext" class="rounded-full border px-3 py-1.5 dark:border-gray-700">Next</button>
      </div>
    </div>
  </section>

  {{-- ====== COACHES ====== --}}
  @if($coaches->isNotEmpty())
  <section class="mx-auto my-[70px] max-w-6xl px-4">
    <h3 class="mb-5 font-orbitron text-2xl sm:text-3xl">Our Coaches</h3>
    <div class="swiper" id="coachesSwiper">
      <div class="swiper-wrapper">
        @foreach($coaches as $c)
          <div class="swiper-slide">
            <a href="{{ route('coaches.show',$c->slug) }}"
               class="block rounded-3xl bg-white p-4 text-center ring-1 ring-black/5 transition hover:shadow-lg dark:bg-gray-900">
              <div class="mx-auto mb-2 h-16 w-16 overflow-hidden rounded-full bg-gray-200 dark:bg-gray-800">
                @if($c->image_path)
                  <x-img :src="Storage::url($c->image_path)" class="h-full w-full object-cover" :alt="$c->name" />
                @endif
              </div>
              <div class="font-semibold line-clamp-1">{{ $c->name }}</div>
              <div class="text-xs text-gray-500 line-clamp-1">{{ $c->team ?? '—' }}</div>
            </a>
          </div>
        @endforeach
      </div>
      <div class="mt-3 flex items-center justify-end gap-2">
        <button id="coachesPrev" class="rounded-full border px-3 py-1.5 dark:border-gray-700">Prev</button>
        <button id="coachesNext" class="rounded-full border px-3 py-1.5 dark:border-gray-700">Next</button>
      </div>
    </div>
  </section>
  @endif

  {{-- ====== ABOUT FEATURE (opzionale) ====== --}}
  @php
    $aboutFeature = \App\Models\AboutSection::featured()->where('layout','image_left')->ordered()->first();
  @endphp
  @if($aboutFeature)
    @include('partials.about-section', ['s' => $aboutFeature])
  @endif

    <div class="my-[30px]"></div>
                      
  {{-- ====== PARTNER STRIP ====== --}}
  <x-partners-slider />

  {{-- ====== CTA ====== --}}
  <section class="full-bleed mt-[90px] w-full bg-[var(--accent)] py-12 text-white">
    <div class="mx-auto grid max-w-6xl items-center gap-8 px-4 md:grid-cols-2">
      <div>
        <h2 class="mb-3 text-2xl font-semibold">Let’s build something great</h2>
        <p class="text-white/90">
          Tell us your goal. We’ll turn it into a repeatable engine.
        </p>
      </div>
      <div class="flex flex-col gap-3 sm:flex-row">
        <a class="w-fit rounded bg-white px-6 py-3 text-black" href="{{ route('contacts') }}">Contact us</a>
        @php $s = \App\Models\SiteSetting::first(); $discord = $s?->discord_url ?? $s?->discord_link ?? '#'; @endphp
        <a href="{{ $discord }}" class="w-fit rounded px-6 py-3 text-white hover:opacity-90" style="background:#212121;">
          Join our Discord
        </a>
      </div>
    </div>
  </section>

  {{-- ====== SWIPER INIT ====== --}}
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      if (!window.Swiper) return;

      new Swiper('#homeHero', {
        loop: true,
        autoplay: { delay: 4500, disableOnInteraction: false },
        pagination: { el: '#homeHero .swiper-pagination', clickable: true },
        navigation: { nextEl: '#homeHero .swiper-button-next', prevEl: '#homeHero .swiper-button-prev' },
      });

      const makeSwiper = (id, prevSel, nextSel, breakpoints) => {
        return new Swiper(id, {
          spaceBetween: 16,
          slidesPerView: 1, // mobile: 1
          breakpoints: breakpoints,
          navigation: prevSel && nextSel ? { prevEl: prevSel, nextEl: nextSel } : undefined,
          watchOverflow: true,
        });
      };

      // Packs & Services: 1 / 2 / 3
      makeSwiper('#packsSwiper',    '#packsPrev',    '#packsNext',    { 640:{slidesPerView:2}, 1024:{slidesPerView:3} });
      makeSwiper('#servicesSwiper', '#servicesPrev', '#servicesNext', { 640:{slidesPerView:2}, 1024:{slidesPerView:3} });

      // Builders & Coaches più compatti: 2 / 3 / 5
      new Swiper('#buildersSwiper', {
        spaceBetween: 16,
        slidesPerView: 1,
        breakpoints: { 480:{slidesPerView:2}, 640:{slidesPerView:3}, 1024:{slidesPerView:5} },
        navigation: { prevEl: '#buildersPrev', nextEl: '#buildersNext' },
        watchOverflow: true,
      });

      new Swiper('#coachesSwiper', {
        spaceBetween: 16,
        slidesPerView: 1,
        breakpoints: { 480:{slidesPerView:2}, 640:{slidesPerView:3}, 1024:{slidesPerView:5} },
        navigation: { prevEl: '#coachesPrev', nextEl: '#coachesNext' },
        watchOverflow: true,
      });
    });
  </script>
</x-app-layout>
