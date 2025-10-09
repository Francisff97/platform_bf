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
    <style>
  /* —— CARD FX —— */
  .neo-card{
    position: relative; overflow: hidden; border-radius: 22px;
    background: linear-gradient(180deg, rgba(255,255,255,.85), rgba(255,255,255,.75));
    box-shadow: 0 12px 40px rgba(0,0,0,.10);
  }
  .dark .neo-card{
    background: linear-gradient(180deg, rgba(17,24,39,.75), rgba(17,24,39,.65));
    box-shadow: 0 12px 40px rgba(0,0,0,.35);
  }
  .neo-ring{
    position: relative;
  }
  .neo-ring::before{
    content:""; position:absolute; inset:-1px; border-radius: 24px;
    padding:1px;
    background:
      radial-gradient(1200px 1200px at var(--mx,50%) var(--my,50%),
        color-mix(in oklab, var(--accent), white 20%) 0,
        transparent 45%),
      linear-gradient(90deg,
        color-mix(in oklab, var(--accent), white 25%),
        color-mix(in oklab, var(--accent), black 25%));
    -webkit-mask: linear-gradient(#000 0 0) content-box, linear-gradient(#000 0 0);
            mask: linear-gradient(#000 0 0) content-box, linear-gradient(#000 0 0);
    -webkit-mask-composite: xor; mask-composite: exclude;
    pointer-events:none;
  }
  .neo-tilt{ transform-style: preserve-3d; will-change: transform }
  .neo-tilt:hover{ transition: transform .08s ease-out }

  /* —— AVATAR HALO —— */
  .avatar-wrap{ position: relative }
  .avatar-wrap::after{
    content:""; position:absolute; inset:-8px; border-radius: 9999px; z-index:0;
    background: radial-gradient(120px 120px at 50% 40%, var(--accent) 0, transparent 70%);
    opacity:.25; filter: blur(12px);
  }

  /* —— CHIPS SCROLLER —— */
  .chips{
    display:flex; gap:.5rem; overflow:auto; scrollbar-width: none; -ms-overflow-style: none;
  }
  .chips::-webkit-scrollbar{ display:none }
  .chip{
    white-space:nowrap; font-size:11px; padding:.35rem .6rem; border-radius:9999px;
    background: color-mix(in oklab, var(--accent), white 85%);
    color: color-mix(in oklab, var(--accent), black 10%);
    border: 1px solid color-mix(in oklab, var(--accent), black 20%);
  }
  .dark .chip{
    background: color-mix(in oklab, var(--accent), black 85%);
    color: color-mix(in oklab, var(--accent), white 15%);
    border-color: color-mix(in oklab, var(--accent), black 40%);
  }
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

  {{-- ====== BUILDERS (neo cards + tilt) ====== --}}
<section class="mx-auto my-[70px] max-w-6xl px-4">
  <div class="mb-5 flex items-end justify-between gap-3">
    <div>
      <h3 class="font-orbitron text-2xl sm:text-3xl">Our Builders</h3>
      <p class="text-sm text-gray-500 dark:text-gray-400">Makers behind the best setups.</p>
    </div>
    <div class="hidden sm:flex items-center gap-2">
      <button id="buildersPrev" class="rounded-full border px-3 py-1.5 dark:border-gray-700">Prev</button>
      <button id="buildersNext" class="rounded-full border px-3 py-1.5 dark:border-gray-700">Next</button>
    </div>
  </div>

  <div class="swiper" id="buildersSwiper">
    <div class="swiper-wrapper">
      @foreach($builders as $b)
        <div class="swiper-slide">
          <a href="{{ route('builders.show',$b->slug) }}"
             class="neo-tilt neo-ring block p-[1px] rounded-[24px] transition-transform">
            <div class="neo-card rounded-[22px] p-5 text-center">
              <div class="avatar-wrap mx-auto mb-3 h-20 w-20 overflow-hidden rounded-full ring-4 ring-white/70 dark:ring-gray-900/60 relative z-10">
                @if($b->image_path)
                  <x-img :src="Storage::url($b->image_path)" class="h-full w-full object-cover" :alt="$b->name" />
                @endif
              </div>
              <div class="font-semibold">{{ $b->name }}</div>
              <div class="text-xs text-gray-500">{{ $b->team ?? '—' }}</div>

              @if($b->skills)
                <div class="chips mx-auto mt-3 max-w-[90%]">
                  @foreach($b->skills as $s)
                    <span class="chip">{{ $s }}</span>
                  @endforeach
                </div>
              @endif
            </div>
          </a>
        </div>
      @endforeach
    </div>
    <div class="mt-3 flex items-center justify-end gap-2 sm:hidden">
      <button id="buildersPrev" class="rounded-full border px-3 py-1.5 dark:border-gray-700">Prev</button>
      <button id="buildersNext" class="rounded-full border px-3 py-1.5 dark:border-gray-700">Next</button>
    </div>
  </div>
</section>

  {{-- ====== COACHES (neo cards + CTA) ====== --}}
@if($coaches->isNotEmpty())
<section class="mx-auto my-[70px] max-w-6xl px-4">
  <div class="mb-5 flex items-end justify-between gap-3">
    <div>
      <h3 class="font-orbitron text-2xl sm:text-3xl">Our Coaches</h3>
      <p class="text-sm text-gray-500 dark:text-gray-400">Learn faster with 1:1 guidance.</p>
    </div>
    <div class="hidden sm:flex items-center gap-2">
      <button id="coachesPrev" class="rounded-full border px-3 py-1.5 dark:border-gray-700">Prev</button>
      <button id="coachesNext" class="rounded-full border px-3 py-1.5 dark:border-gray-700">Next</button>
    </div>
  </div>

  <div class="swiper" id="coachesSwiper">
    <div class="swiper-wrapper">
      @foreach($coaches as $c)
        <div class="swiper-slide">
          <a href="{{ route('coaches.show',$c->slug) }}"
             class="neo-tilt neo-ring block p-[1px] rounded-[24px] transition-transform">
            <div class="neo-card rounded-[22px] p-5 text-center">
              <div class="avatar-wrap mx-auto mb-3 h-20 w-20 overflow-hidden rounded-full ring-4 ring-white/70 dark:ring-gray-900/60 relative z-10">
                @if($c->image_path)
                  <x-img :src="Storage::url($c->image_path)" class="h-full w-full object-cover" :alt="$c->name" />
                @endif
              </div>

              <div class="font-semibold">{{ $c->name }}</div>
              <div class="text-xs text-gray-500">{{ $c->team ?? '—' }}</div>

              @if(!empty($c->skills) && is_iterable($c->skills))
                <div class="chips mx-auto mt-3 max-w-[90%]">
                  @foreach($c->skills as $s)
                    <span class="chip">{{ $s }}</span>
                  @endforeach
                </div>
              @endif

              <div class="mt-4">
                <span class="inline-flex items-center gap-2 rounded-full bg-[color:var(--accent)]/90 px-3 py-1.5 text-xs font-semibold text-white">
                  Book session
                  <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                </span>
              </div>
            </div>
          </a>
        </div>
      @endforeach
    </div>
    <div class="mt-3 flex items-center justify-end gap-2 sm:hidden">
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

    <div class="my-[50px]"></div>
                      
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
      function attachTilt(root){
    root.querySelectorAll('.neo-tilt').forEach(card => {
      const ringHost = card; // ha ::before con il gradient ring
      const inner = card.querySelector('.neo-card');
      let raf = 0;

      function onMove(e){
        const r = card.getBoundingClientRect();
        const x = (e.clientX - r.left) / r.width;
        const y = (e.clientY - r.top)  / r.height;
        cancelAnimationFrame(raf);
        raf = requestAnimationFrame(() => {
          const rx = (y - .5) * -10;
          const ry = (x - .5) *  10;
          inner.style.transform = `perspective(800px) rotateX(${rx}deg) rotateY(${ry}deg) translateZ(0)`;
          ringHost.style.setProperty('--mx', `${x*100}%`);
          ringHost.style.setProperty('--my', `${y*100}%`);
        });
      }
      function reset(){
        cancelAnimationFrame(raf);
        inner.style.transform = `perspective(800px) rotateX(0deg) rotateY(0deg)`;
      }
      card.addEventListener('mousemove', onMove, {passive:true});
      card.addEventListener('mouseleave', reset);
      card.addEventListener('touchend', reset);
    });
  }

  document.addEventListener('DOMContentLoaded', () => {
    // attacca tilt alle slide renderizzate all’avvio
    attachTilt(document);

    // quando Swiper cambia, ri-attacca (le slide sono clonate)
    ['buildersSwiper','coachesSwiper'].forEach(id => {
      const swEl = document.getElementById(id);
      if (!swEl || !swEl.swiper) return;
      swEl.swiper.on('slideChangeTransitionEnd', () => attachTilt(swEl));
      swEl.swiper.on('resize', () => attachTilt(swEl));
    });
  });
  </script>
</x-app-layout>
