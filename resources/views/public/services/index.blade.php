<x-app-layout>
  <x-auto-hero/>

  {{-- HERO / INTRO --}}
  <section class="relative mx-auto mt-[60px] max-w-5xl text-center">
    <h2 class="text-4xl font-bold tracking-tight text-gray-900 dark:text-white sm:text-5xl">Our Services</h2>
    <p class="mx-auto mt-4 max-w-2xl text-gray-600 dark:text-gray-300 text-lg">
      Discover the range of services we provide to help you build, grow, and elevate your digital experience.
      Each service is designed with quality, precision, and innovation in mind.
    </p>
  </section>

  {{-- GRID SERVICES --}}
  <div class="mx-auto mt-16 grid max-w-6xl grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-3 px-4">
    @foreach($services as $s)
      <div class="group relative overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm transition
                  hover:-translate-y-1 hover:shadow-lg dark:border-gray-800 dark:bg-gray-900/70">

        <div class="relative h-48 w-full overflow-hidden">
          @if($s->image_url)
            <x-img :src="$s->image_url" class="h-full w-full object-cover" />
            <div class="absolute inset-0 bg-gradient-to-t from-black/50 via-black/10 to-transparent opacity-0 transition-opacity duration-500 group-hover:opacity-80"></div>
          @else
            <div class="h-full w-full bg-gray-200 dark:bg-gray-800"></div>
          @endif
        </div>

        <div class="relative z-10 p-5">
          <h3 class="text-xl font-semibold text-gray-900 dark:text-white transition-colors group-hover:text-[color:var(--accent)]">
            {{ $s->name }}
          </h3>

          @if(!empty($s->excerpt))
            <p class="mt-2 text-sm text-[color:var(--accent)]">{{ $s->excerpt }}</p>
          @endif

          @if(!empty($s->body))
            <p class="mt-3 text-sm text-gray-600 dark:text-gray-300 line-clamp-3">{{ strip_tags($s->body) }}</p>
          @endif
        </div>

        <div class="absolute bottom-0 left-0 h-1 w-0 bg-[color:var(--accent)] transition-all duration-500 group-hover:w-full"></div>
      </div>
    @endforeach
  </div>

  <div class="pointer-events-none absolute inset-x-0 top-0 -z-10 h-[400px] bg-gradient-to-b from-[color:var(--accent)]/10 to-transparent"></div>
</x-app-layout>