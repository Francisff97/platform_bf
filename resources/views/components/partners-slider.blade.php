@props(['partners' => $partners])

<section class="mx-auto my-[90px] max-w-6xl px-4">
  <div class="mb-8 text-center">
    <h2 class="font-orbitron text-2xl sm:text-3xl">Our Partners</h2>
    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Innovating together with our trusted allies.</p>
  </div>

  <div
    x-data="{
      scrollBy(step){ $refs.track?.scrollBy({ left: step, behavior: 'smooth' }); },
      page(){ return Math.round(($refs.wrap?.clientWidth || 0) * 0.8); }
    }"
    class="relative"
  >
    {{-- Track orizzontale --}}
    <div x-ref="wrap" class="relative">
      <div
        x-ref="track"
        class="hide-scroll flex snap-x snap-mandatory gap-6 overflow-x-auto scroll-smooth p-1"
        onwheel="this.scrollLeft += event.deltaY"
        role="region" aria-roledescription="carousel" aria-label="Partners"
      >
        @foreach($partners as $p)
          <div class="snap-center w-[68%] shrink-0 sm:w-[42%] md:w-[27%] lg:w-[20%]">
            <div class="group relative flex h-full flex-col overflow-hidden rounded-2xl border border-transparent
                        bg-gradient-to-b from-white/90 to-white/70 p-5 text-center shadow-sm backdrop-blur-md
                        transition hover:-translate-y-1 hover:shadow-lg dark:from-gray-900/80 dark:to-gray-800/70">

              <div class="mx-auto h-20 w-20 overflow-hidden rounded-full ring-2 ring-[color:var(--accent)]/20">
                @if($p->logo_path)
                  <x-img :src="Storage::url($p->logo_path)" :alt="$p->name" class="h-full w-full object-cover" />
                @else
                  <div class="grid h-full w-full place-items-center text-xs text-gray-400">No logo</div>
                @endif
              </div>

              <div class="mt-3 grow">
                <div class="font-medium text-gray-800 dark:text-gray-100">{{ $p->name }}</div>
              </div>

              @if($p->url)
                <a href="{{ $p->url }}" target="_blank" rel="noopener"
                   class="mt-3 inline-flex items-center justify-center gap-2 rounded-full px-4 py-2 text-sm
                          text-white transition hover:opacity-90"
                   style="background:var(--accent)">
                  Visit site
                  <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path d="M5 12h14M12 5l7 7-7 7"/>
                  </svg>
                </a>
              @endif
            </div>
          </div>
        @endforeach
      </div>
    </div>

    {{-- Controls: sopra a tutto, clickabili --}}
    <div class="absolute inset-y-0 left-0 right-0 z-20 flex items-center justify-between pointer-events-none">
      <button type="button" aria-label="Previous"
              class="pointer-events-auto ml-2 rounded-full bg-white/90 p-2 text-black shadow transition hover:scale-110
                     dark:bg-black/70 dark:text-white"
              @click="scrollBy(-page())">
        ‹
      </button>
      <button type="button" aria-label="Next"
              class="pointer-events-auto mr-2 rounded-full bg-white/90 p-2 text-black shadow transition hover:scale-110
                     dark:bg-black/70 dark:text-white"
              @click="scrollBy(page())">
        ›
      </button>
    </div>
  </div>

  <style>
    .hide-scroll::-webkit-scrollbar{display:none}
    .hide-scroll{ -ms-overflow-style:none; scrollbar-width:none }
  </style>
</section>
