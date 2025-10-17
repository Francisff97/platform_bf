{{-- resources/views/home.blade.php --}}
<x-app-layout>
{{-- ====== HERO FULL-BLEED (slides) — versione base ====== --}}
<style>
  .full-bleed{
    width:100vw;position:relative;left:50%;right:50%;
    margin-left:-50vw;margin-right:-50vw;overflow:hidden;
  }

  /* Contenitori Swiper */
  #homeHero{ overflow:hidden; }
  #homeHero .swiper { overflow: hidden; }
  #homeHero .swiper-wrapper{ align-items:stretch; }

  /* Safari fix: la slide deve avere una propria altezza */
  #homeHero .swiper-slide{
    display:block;             /* niente flex strani */
    height:auto;               /* lascia che il figlio imposti l'altezza */
    backface-visibility:hidden;
    -webkit-transform:translateZ(0);
            transform:translateZ(0);
    contain: layout paint style; /* evita reflow strani tra slide */
  }

  /* La “figcaption” che usi resta assoluta sopra, ma
     la figura fornisce l’altezza alla slide */
  #homeHero .slide-figure{
    position:relative;
    height:70vh;min-height:480px;margin:0;
  }
  @supports (height:70svh){
    #homeHero .slide-figure{ height:70svh; }
  }
  @media (max-width:767px){
    #homeHero .slide-figure{ height:400px;min-height:400px; }
  }

  /* L’immagine può rimanere assoluta */
  #homeHero .slide-figure > img{
    position:absolute;inset:0;width:100%;height:100%;
    object-fit:cover;display:block;
  }

  /* UI */
  #homeHero .swiper-button-next,#homeHero .swiper-button-prev{ color:#fff; }
  #homeHero .swiper-pagination-bullet{ background:rgba(255,255,255,.6);opacity:1; }
  #homeHero .swiper-pagination-bullet-active{ background:#fff; }
</style>

<section class="full-bleed">
  <div id="homeHero" class="swiper w-full">
    <div class="swiper-wrapper">

      @foreach($slides as $s)
        @php
          $path = $s->image_path ?? null;
          $src  = $path ? img_url($path, 1920, 1080) : null;   // URL ottimizzato (CF/fallback)
          $alt  = img_alt($s) ?: ($s->title ?? 'Slide');       // ALT dal backend SEO
        @endphp

        {{-- Preload SOLO la prima (LCP) con lo stesso URL usato sotto --}}
        @if($loop->first && $src)
          <link rel="preload" as="image" href="{{ $src }}" fetchpriority="high">
        @endif

        <div class="swiper-slide">
          <figure class="slide-figure relative w-full">
            @if($src)
              <img
                src="{{ $src }}"
                alt="{{ $alt }}"
                width="1920" height="1080"
                class="absolute inset-0 h-full w-full object-cover"
                loading="{{ $loop->first ? 'eager' : 'lazy' }}"
                {{ $loop->first ? 'fetchpriority=high importance=high' : '' }}
                decoding="async">
            @else
              <div class="absolute inset-0 bg-gradient-to-r from-gray-200 to-gray-300 dark:from-gray-900 dark:to-gray-800"></div>
            @endif

            <div class="absolute inset-0 bg-gradient-to-b from-black/55 via-black/35 to-black/60 pointer-events-none"></div>

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
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                      <path d="M5 12h14M12 5l7 7-7 7"/>
                    </svg>
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

 <script>
  document.addEventListener('DOMContentLoaded', () => {
    if (!window.Swiper) return;

    const hero = new Swiper('#homeHero', {
      loop: true,
      speed: 650,
      // evita che Swiper aspetti le immagini per calcolare l'altezza
      preloadImages: false,
      // carica la prossima/precedente appena parte la transizione
      lazy: { loadPrevNext: true, loadOnTransitionStart: true },
      // osserva cambi di layout (Safari fix)
      observer: true,
      observeParents: true,
      watchSlidesProgress: true,
      // nav/pagination
      pagination: { el: '#homeHero .swiper-pagination', clickable: true },
      navigation: { nextEl: '#homeHero .swiper-button-next', prevEl: '#homeHero .swiper-button-prev' },
      on: {
        init(sw){ sw.updateAutoHeight(0); },
        imagesReady(sw){ sw.update(); },
        slideChange(sw){ sw.update(); }
      }
    });
  });
</script>
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
                <x-img :src="Storage::url($s->image_path)" :alt="$s->alt_text ?? $s->title ?? $s->name ?? 'Service image'" class="aspect-[16/9] w-full object-cover" />
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
  <section class="mx-auto my-[90px] max-w-6xl px-4">
    <div class="mb-6 flex items-end justify-between gap-3">
      <div>
        <h3 class="font-orbitron text-2xl sm:text-3xl">Our Builders</h3>
        <p class="text-sm text-gray-500 dark:text-gray-400">Meet the makers shaping the meta.</p>
      </div>
    </div>

    <div class="swiper" id="buildersSwiper">
      <div class="swiper-wrapper">
        @foreach($builders as $b)
          <div class="swiper-slide">
            <a href="{{ route('builders.show',$b->slug) }}"
               class="group relative flex flex-col items-center justify-between overflow-hidden rounded-[22px] border border-transparent 
                      bg-gradient-to-b from-white/90 to-white/70 p-5 shadow-sm backdrop-blur-md transition hover:-translate-y-1 hover:shadow-lg
                      dark:from-gray-900/70 dark:to-gray-800/70">
              
              <div class="absolute inset-0 bg-gradient-to-br from-[color:var(--accent)]/10 to-transparent opacity-0 transition group-hover:opacity-100"></div>

              <div class="relative mb-4 h-20 w-20 overflow-hidden rounded-full ring-4 ring-white/60 dark:ring-gray-900/60">
                @if($b->image_path)
                  <x-img :src="Storage::url($b->image_path)" class="h-full w-full object-cover" :alt="$b->name" />
                @endif
              </div>

              <h4 class="text-base font-semibold text-gray-900 dark:text-white">{{ $b->name }}</h4>
              <p class="text-xs text-gray-500 dark:text-gray-400">{{ $b->team ?? '—' }}</p>

              @if($b->skills)
                <div class="mt-3 flex flex-wrap justify-center gap-2">
                  @foreach($b->skills as $s)
                    <span class="rounded-full bg-[color:var(--accent)]/10 px-3 py-1 text-[11px] font-medium text-[color:var(--accent)]
                                   ring-1 ring-[color:var(--accent)]/20 backdrop-blur-sm">
                      {{ $s }}
                    </span>
                  @endforeach
                </div>
              @endif
            </a>
          </div>
        @endforeach
      </div>
    </div>
  </section>

  {{-- ====== COACHES ====== --}}
  @if($coaches->isNotEmpty())
    <section class="mx-auto my-[90px] max-w-6xl px-4">
      <div class="mb-6 flex items-end justify-between gap-3">
        <div>
          <h3 class="font-orbitron text-2xl sm:text-3xl">Our Coaches</h3>
          <p class="text-sm text-gray-500 dark:text-gray-400">Learn faster with 1:1 guidance.</p>
        </div>
      </div>

      <div class="swiper" id="coachesSwiper">
        <div class="swiper-wrapper">
          @foreach($coaches as $c)
            <div class="swiper-slide">
              <a href="{{ route('coaches.show',$c->slug) }}"
                 class="group relative flex flex-col items-center justify-between overflow-hidden rounded-[22px] border border-transparent
                        bg-gradient-to-b from-white/90 to-white/70 p-5 shadow-sm backdrop-blur-md transition hover:-translate-y-1 hover:shadow-lg
                        dark:from-gray-900/70 dark:to-gray-800/70">
                
                <div class="absolute inset-0 bg-gradient-to-br from-[color:var(--accent)]/10 to-transparent opacity-0 transition group-hover:opacity-100"></div>

                <div class="relative mb-4 h-20 w-20 overflow-hidden rounded-full ring-4 ring-white/60 dark:ring-gray-900/60">
                  @if($c->image_path)
                    <x-img :src="Storage::url($c->image_path)" class="h-full w-full object-cover" :alt="$c->name" />
                  @endif
                </div>

                <h4 class="text-base font-semibold text-gray-900 dark:text-white">{{ $c->name }}</h4>
                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $c->team ?? '—' }}</p>

                @if($c->skills)
                  <div class="mt-3 flex flex-wrap justify-center gap-2">
                    @foreach($c->skills as $s)
                      <span class="rounded-full bg-[color:var(--accent)]/10 px-3 py-1 text-[11px] font-medium text-[color:var(--accent)]
                                     ring-1 ring-[color:var(--accent)]/20 backdrop-blur-sm">
                        {{ $s }}
                      </span>
                    @endforeach
                  </div>
                @endif

                <div class="mt-4 inline-flex items-center gap-1 text-[12px] font-semibold text-[color:var(--accent)] transition group-hover:translate-x-1">
                  Book session
                  <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                </div>
              </a>
            </div>
          @endforeach
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
  <section class="full-bleed relative mt-[90px] w-full overflow-hidden py-14 text-white" style="background: var(--accent)">
    <div class="pointer-events-none absolute inset-0 opacity-20">
      <div class="absolute -top-24 -left-24 h-72 w-72 rounded-full blur-3xl" style="background: rgba(255,255,255,.25)"></div>
      <div class="absolute -bottom-24 -right-24 h-72 w-72 rounded-full blur-3xl" style="background: rgba(0,0,0,.25)"></div>
    </div>

    <div class="relative mx-auto grid max-w-6xl items-center gap-8 px-4 md:grid-cols-2">
      <div>
        <h2 class="font-orbitron text-3xl sm:text-4xl">Let’s build something great</h2>
        <p class="mt-3 text-white/90 text-sm sm:text-base">Tell us your goal. We’ll turn it into a repeatable engine.</p>
      </div>

      <div class="flex flex-col gap-3 sm:flex-row sm:justify-end">
        <a href="{{ route('contacts') }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-white px-6 py-3 text-black shadow-lg ring-1 ring-black/5 transition hover:translate-y-[-1px] hover:shadow-xl active:translate-y-0">
          Contact us
          <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
        </a>

        @php
          $s = \App\Models\SiteSetting::first();
          $discord = $s?->discord_url ?? $s?->discord_link ?? '#';
        @endphp
        <a href="{{ $discord }}" target="_blank" rel="noopener" class="inline-flex items-center justify-center gap-2 rounded-xl px-6 py-3 text-white shadow-lg ring-1 ring-white/20 transition hover:translate-y-[-1px] hover:shadow-xl active:translate-y-0" style="background:#1f2937;">
          <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
            <path d="M20 4.4a18 18 0 0 0-4.5-1.4l-.2.5a16 16 0 0 1 3.8 1.3c-3.4-1.6-7.1-1.6-10.5 0A16 16 0 0 1 12.9 3l-.2-.5C8.4 3 6 4 6 4s-5 7.3-5 13.1C3.7 19.7 6.2 20 6.2 20l1.1-1.6c-.6-.2-1.2-.5-1.8-.8l.4-.3c3.6 1.7 7.8 1.7 11.4 0l.4.3c-.6.3-1.2.6-1.8.8L17.8 20s2.5-.3 5.2-2.9C25 11.3 20 4.4 20 4.4Zm-9.5 9.2c-.9 0-1.6-.8-1.6-1.7 0-.9.7-1.6 1.6-1.6.9 0 1.7.7 1.7 1.6 0 .9-.8 1.7-1.7 1.7Zm6 0c-.9 0-1.6-.8-1.6-1.7 0-.9.7-1.6 1.6-1.6s1.7.7 1.7 1.6c0 .9-.8 1.7-1.7 1.7Z"/>
          </svg>
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
          slidesPerView: 1,
          breakpoints: breakpoints,
          navigation: prevSel && nextSel ? { prevEl: prevSel, nextEl: nextSel } : undefined,
          watchOverflow: true,
        });
      };

      makeSwiper('#packsSwiper',    '#packsPrev',    '#packsNext',    { 640:{slidesPerView:2}, 1024:{slidesPerView:3} });
      makeSwiper('#servicesSwiper', '#servicesPrev', '#servicesNext', { 640:{slidesPerView:2}, 1024:{slidesPerView:3} });

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
        const ringHost = card;
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
      attachTilt(document);
      ['buildersSwiper','coachesSwiper'].forEach(id => {
        const swEl = document.getElementById(id);
        if (!swEl || !swEl.swiper) return;
        swEl.swiper.on('slideChangeTransitionEnd', () => attachTilt(swEl));
        swEl.swiper.on('resize', () => attachTilt(swEl));
      });
    });
  </script>
</x-app-layout>