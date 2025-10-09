@props(['partners' => $partners])

<section class="mx-auto my-[90px] max-w-6xl px-4">
  <div class="mb-10 text-center">
    <h2 class="font-orbitron text-2xl sm:text-3xl">Our Partners</h2>
    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Innovating together with our trusted allies.</p>
  </div>

  <div 
    x-data="{ scroll: 0 }" 
    class="relative overflow-hidden"
  >
    {{-- Gradient edges --}}
    <div class="pointer-events-none absolute inset-y-0 left-0 w-32 bg-gradient-to-r from-[var(--bg)] via-[var(--bg)]/80 to-transparent dark:from-gray-900"></div>
    <div class="pointer-events-none absolute inset-y-0 right-0 w-32 bg-gradient-to-l from-[var(--bg)] via-[var(--bg)]/80 to-transparent dark:from-gray-900"></div>

    {{-- Scrollable row --}}
    <div x-ref="track" class="flex snap-x snap-mandatory gap-6 overflow-x-auto scroll-smooth p-4 hide-scroll"
         onwheel="this.scrollLeft += event.deltaY">

      @foreach($partners as $p)
        <div class="snap-center shrink-0 w-[65%] sm:w-[40%] md:w-[25%] lg:w-[18%]">
          <div class="group relative overflow-hidden rounded-2xl border border-transparent bg-gradient-to-b from-white/90 to-white/70 p-5
                      text-center shadow-sm backdrop-blur-md transition hover:-translate-y-1 hover:shadow-lg
                      dark:from-gray-900/80 dark:to-gray-800/70">
            <div class="absolute inset-0 bg-gradient-to-br from-[color:var(--accent)]/10 to-transparent opacity-0 transition group-hover:opacity-100"></div>

            <div class="mx-auto h-20 w-20 overflow-hidden rounded-full ring-2 ring-[color:var(--accent)]/20">
              @if($p->logo_path)
                <x-img :src="Storage::url($p->logo_path)" class="h-full w-full object-cover" :alt="$p->name" />
              @else
                <div class="grid h-full w-full place-items-center text-xs text-gray-400">No logo</div>
              @endif
            </div>

            <div class="mt-3 font-medium text-gray-800 dark:text-gray-100">{{ $p->name }}</div>
            @if($p->url)
              <a href="{{ $p->url }}" target="_blank" rel="noopener"
                 class="mt-1 inline-flex items-center gap-1 text-xs font-semibold text-[color:var(--accent)] hover:underline">
                Visit
                <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M12 5l7 7-7 7M5 12h14"/></svg>
              </a>
            @endif
          </div>
        </div>
      @endforeach
    </div>

    {{-- Controls --}}
    <div class="pointer-events-none absolute inset-y-0 left-0 right-0 flex items-center justify-between">
      <button type="button"
              class="pointer-events-auto ml-2 rounded-full bg-white/80 p-2 text-black shadow transition hover:scale-110 dark:bg-black/70 dark:text-white"
              @click="$refs.track.scrollBy({left:-($refs.track.clientWidth*0.8), behavior:'smooth'})">
        ‹
      </button>
      <button type="button"
              class="pointer-events-auto mr-2 rounded-full bg-white/80 p-2 text-black shadow transition hover:scale-110 dark:bg-black/70 dark:text-white"
              @click="$refs.track.scrollBy({left:($refs.track.clientWidth*0.8), behavior:'smooth'})">
        ›
      </button>
    </div>
  </div>

  <style>
    .hide-scroll::-webkit-scrollbar { display: none; }
    .hide-scroll { -ms-overflow-style: none; scrollbar-width: none; }
  </style>
</section>
