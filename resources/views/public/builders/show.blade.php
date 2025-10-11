<x-app-layout>
    @php
    ($seoSubject = $builder)
    $title = $builder->name;
  @endphp

  {{-- HERO minimal con gradient --}}
  <section class="relative isolate">
    <div class="pointer-events-none absolute inset-0 -z-10 bg-gradient-to-b from-[var(--accent)]/15 via-transparent to-transparent"></div>

    <div class="mx-auto max-w-6xl px-4 pt-10 sm:pt-14">
      <h1 class="text-3xl font-extrabold leading-tight text-gray-900 dark:text-gray-100 sm:text-4xl">
        {{ $title }}
      </h1>
      @if($builder->team)
        <div class="mt-1 text-sm text-gray-600 dark:text-gray-300">
          Team: <span class="font-medium">{{ $builder->team }}</span>
        </div>
      @endif
    </div>
  </section>

  {{-- TESTATA con avatar + info --}}
  <section class="mx-auto max-w-6xl grid grid-cols-1 gap-8 px-4 py-8 md:grid-cols-3">
    {{-- Avatar / immagine --}}
    <div class="md:col-span-1">
      <div class="overflow-hidden rounded-2xl ring-1 ring-black/5 dark:ring-white/10">
        @if($builder->image_path)
          <x-img :src="Storage::url($builder->image_path)"
                 alt="{{ $builder->name }}"
                 class="aspect-[4/3] w-full object-cover" />
        @else
          <div class="aspect-[4/3] w-full rounded-2xl bg-gray-200 dark:bg-gray-800"></div>
        @endif
      </div>

      {{-- Skills --}}
      @if(is_iterable($builder->skills) && count($builder->skills))
        <div class="mt-4">
          <div class="text-sm font-medium text-gray-800 dark:text-gray-200">Skills</div>
          <div class="mt-2 flex flex-wrap gap-2">
            @foreach($builder->skills as $s)
              <span class="rounded-full bg-gray-100 px-2.5 py-1 text-xs font-medium
                           dark:bg-gray-800 dark:text-gray-100">
                {{ $s }}
              </span>
            @endforeach
          </div>
        </div>
      @endif
    </div>

    {{-- Bio / Descrizione --}}
    <div class="md:col-span-2">
      @if($builder->description)
        <div class="rounded-2xl border border-gray-100 bg-white/70 p-6 shadow-sm backdrop-blur
                    dark:border-gray-800 dark:bg-gray-900/60">
          <div class="prose max-w-none prose-p:leading-relaxed dark:prose-invert">
            {!! nl2br(e($builder->description)) !!}
          </div>
        </div>
      @else
        <div class="rounded-2xl border border-dashed border-gray-300 p-6 text-sm text-gray-600 dark:border-gray-700 dark:text-gray-300">
          This builder hasn’t added a bio yet.
        </div>
      @endif
    </div>
  </section>

  {{-- PACKS del builder --}}
  <section class="mx-auto max-w-6xl px-4 pb-12">
    <h2 class="mb-3 mt-4 text-lg font-semibold text-gray-900 dark:text-gray-100">
      Packs by {{ $builder->name }}
    </h2>

    @if($packs->count())
      <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
        @foreach($packs as $p)
          <x-pack-card :pack="$p" />
        @endforeach
      </div>

      <div class="mt-6">{{ $packs->links() }}</div>
    @else
      <div class="rounded-2xl border border-dashed border-gray-300 p-10 text-center dark:border-gray-700">
        <div class="mx-auto max-w-md">
          <h3 class="font-semibold text-gray-900 dark:text-gray-100">No packs yet</h3>
          <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">This builder hasn’t published any packs.</p>
        </div>
      </div>
    @endif
  </section>
</x-app-layout>
