@props(['partners' => $partners])

{{-- Slider senza dipendenze: scroll-snap + controlli Alpine --}}
<div x-data class="relative mx-auto w-full max-w-6xl">
  <div class="mb-6 text-center">
    <h2 class="text-2xl font-bold">Our Partners</h2>
  </div>

  <div class="relative">
    {{-- Track --}}
    <div x-ref="track"
         class="flex snap-x snap-mandatory overflow-x-auto scroll-smooth gap-6 p-2"
         style="-ms-overflow-style:none; scrollbar-width:none;"
         onwheel="this.scrollLeft += event.deltaY"
    >
      {{-- nascondo scrollbar --}}
      <style>.hide-scroll::-webkit-scrollbar{display:none}</style>

      @foreach($partners as $p)
        <div class="hide-scroll snap-start shrink-0 w-[70%] sm:w-[45%] md:w-[30%] lg:w-[20%]">
          <div class="group rounded-2xl border border-gray-200 bg-white p-5 text-center shadow-sm transition
                      hover:-translate-y-1 hover:shadow-lg dark:border-gray-800 dark:bg-gray-900">
            <div class="mx-auto h-20 w-20 overflow-hidden rounded-full ring-1 ring-black/5 dark:ring-white/10">
              @if($p->logo_path)
                <img src="{{ Storage::url($p->logo_path) }}" alt="{{ $p->name }}" class="h-full w-full object-cover">
              @else
                <div class="grid h-full w-full place-items-center text-xs text-gray-400">No logo</div>
              @endif
            </div>
            <div class="mt-3 text-sm font-medium">{{ $p->name }}</div>
            @if($p->url)
              <a href="{{ $p->url }}" target="_blank" rel="noopener"
                 class="mt-1 inline-block text-xs text-[color:var(--accent)] hover:underline">Visit</a>
            @endif
          </div>
        </div>
      @endforeach
    </div>

    {{-- Controls --}}
    <div class="pointer-events-none absolute inset-y-0 left-0 right-0 flex items-center justify-between">
      <button type="button" class="pointer-events-auto ml-1 rounded-full bg-white/80 p-2 shadow dark:bg-black/60"
              @click="$refs.track.scrollBy({left:-($refs.track.clientWidth*0.8), behavior:'smooth'})" aria-label="Prev">
        ‹
      </button>
      <button type="button" class="pointer-events-auto mr-1 rounded-full bg-white/80 p-2 shadow dark:bg-black/60"
              @click="$refs.track.scrollBy({left:($refs.track.clientWidth*0.8), behavior:'smooth'})" aria-label="Next">
        ›
      </button>
    </div>
  </div>
</div>
