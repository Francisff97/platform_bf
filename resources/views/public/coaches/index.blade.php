<x-app-layout>
 <x-auto-hero/>

  {{-- INTRO --}}
  <section class="relative mx-auto mt-[60px] max-w-4xl text-center px-4">
    <h2 class="text-4xl font-bold tracking-tight text-gray-900 dark:text-white sm:text-5xl">
      Our Coaches
    </h2>
    <p class="mx-auto mt-4 max-w-2xl text-lg text-gray-600 dark:text-gray-300">
      Meet the experts ready to guide you. Pick your coach, learn faster, level up.
    </p>
  </section>

  {{-- GRID COACHES --}}
  <section class="mx-auto mt-12 max-w-5xl px-4">
    <div class="grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-3 justify-items-center">
      @foreach($coaches as $c)
        <a href="{{ route('coaches.show',$c->slug) }}"
           class="group relative flex w-full max-w-[320px] flex-col items-center overflow-hidden rounded-2xl border border-gray-100
                  bg-white/90 p-6 text-center shadow-sm transition hover:-translate-y-1 hover:shadow-lg
                  dark:border-gray-800 dark:bg-gray-900/70">

          {{-- Avatar --}}
          <div class="relative mb-4 h-28 w-28 overflow-hidden rounded-full ring-4 ring-white/80 dark:ring-gray-900/70 shadow-lg">
            @if($c->image_path)
              <x-img :src="Storage::url($c->image_path)" class="h-full w-full object-cover" :alt="$c->name" />
            @else
              <div class="h-full w-full bg-gradient-to-r from-gray-200 to-gray-300 dark:from-gray-800 dark:to-gray-700"></div>
            @endif
          </div>

          {{-- Name & Team --}}
          <h3 class="text-lg font-semibold text-gray-900 transition-colors group-hover:text-[color:var(--accent)] dark:text-white">
            {{ $c->name }}
          </h3>
          <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $c->team ?? '—' }}</div>

          {{-- Skills chips centrali --}}
          @if(!empty($c->skills) && is_iterable($c->skills))
            <div class="mt-4 flex flex-wrap justify-center gap-2">
              @foreach($c->skills as $s)
                <span class="rounded-full bg-gray-100 px-2.5 py-1 text-[11px] font-medium text-gray-700 ring-1 ring-gray-200
                             transition group-hover:ring-[color:var(--accent)]
                             dark:bg-gray-800 dark:text-gray-200 dark:ring-gray-700">
                  {{ $s }}
                </span>
              @endforeach
            </div>
          @endif

          {{-- Accent underline animata --}}
          <div class="absolute bottom-0 left-0 h-1 w-0 bg-[color:var(--accent)] transition-all duration-500 group-hover:w-full"></div>
        </a>
      @endforeach
    </div>

    <div class="mt-8">{{ $coaches->links() }}</div>
  </section>

  {{-- sfondo soft --}}
  <div class="pointer-events-none absolute inset-x-0 top-0 -z-10 h-[360px] bg-gradient-to-b from-[color:var(--accent)]/8 to-transparent"></div>
</x-app-layout>
