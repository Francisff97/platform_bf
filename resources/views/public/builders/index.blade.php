<x-app-layout>
  <x-auto-hero/>

  <section class="relative mx-auto mt-[60px] max-w-5xl text-center px-4">
    <h2 class="text-4xl font-bold tracking-tight text-gray-900 dark:text-white sm:text-5xl">Our Builders</h2>
    <p class="mx-auto mt-4 max-w-2xl text-lg text-gray-600 dark:text-gray-300">
      Makers, tinkerers, product minds. Explore who’s crafting the packs you love.
    </p>
  </section>

  <section class="mx-auto mt-12 max-w-6xl px-4">
    <div class="grid grid-cols-1 gap-7 sm:grid-cols-2 lg:grid-cols-3">
      @foreach($builders as $b)
        <a href="{{ route('builders.show',$b->slug) }}"
           class="group relative overflow-hidden rounded-2xl border border-gray-100 bg-white/90 shadow-sm transition
                  hover:-translate-y-1 hover:shadow-lg dark:border-gray-800 dark:bg-gray-900/70">

          {{-- Cover image (URL diretto da Storage) --}}
          @php
            $img = $b->image_path
              ? \Illuminate\Support\Facades\Storage::disk('public')->url($b->image_path)
              : null;
          @endphp

          @if($img)
            <div class="relative h-44 w-full overflow-hidden">
              <img
                src="{{ $img }}"
                alt="{{ $b->name ?: 'Builder' }}"
                class="h-full w-full object-cover transition duration-500 group-hover:scale-[1.03]"
                width="720" height="300"
                loading="lazy" decoding="async"
              >
              <div class="absolute inset-0 bg-gradient-to-t from-black/50 via-black/10 to-transparent opacity-0
                          transition-opacity duration-500 group-hover:opacity-80"></div>
            </div>
          @else
            <div class="h-44 w-full bg-gradient-to-br from-gray-200 to-gray-300 dark:from-gray-800 dark:to-gray-700"></div>
          @endif

          {{-- Texts --}}
          <div class="relative z-10 p-5">
            <div class="flex items-center justify-between gap-3">
              <h3 class="line-clamp-1 text-lg font-semibold text-gray-900 transition-colors group-hover:text-[color:var(--accent)] dark:text-white">
                {{ $b->name }}
              </h3>
              @if($b->team)
                <span class="rounded-full bg-gray-100 px-2.5 py-1 text-[11px] font-medium text-gray-700 ring-1 ring-gray-200
                               dark:bg-gray-800 dark:text-gray-200 dark:ring-gray-700">
                  {{ $b->team }}
                </span>
              @endif
            </div>

            @if(!empty($b->skills) && is_iterable($b->skills))
              <div class="mt-3 -mx-2 overflow-x-auto scrollbar-thin scrollbar-thumb-gray-300 dark:scrollbar-thumb-gray-700">
                <div class="flex gap-2 px-2">
                  @foreach($b->skills as $s)
                    <span class="whitespace-nowrap rounded-full bg-gray-100 px-2.5 py-1 text-[11px] font-medium
                                 text-gray-700 ring-1 ring-gray-200 transition group-hover:ring-[color:var(--accent)]
                                 dark:bg-gray-800 dark:text-gray-200 dark:ring-gray-700">
                      {{ $s }}
                    </span>
                  @endforeach
                </div>
              </div>
            @endif
          </div>

          <div class="absolute bottom-0 left-0 h-1 w-0 bg-[color:var(--accent)] transition-all duration-500 group-hover:w-full"></div>
        </a>
      @endforeach
    </div>

    <div class="mt-8">{{ $builders->links() }}</div>
  </section>

  <div class="pointer-events-none absolute inset-x-0 top-0 -z-10 h-[360px] bg-gradient-to-b from-[color:var(--accent)]/8 to-transparent"></div>
</x-app-layout>