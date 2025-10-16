<x-app-layout>
  @php ($seoSubject = $builder); $title = $builder->name; @endphp

  <section class="relative isolate">
    <div class="pointer-events-none absolute inset-0 -z-10 bg-gradient-to-b from-[var(--accent)]/15 via-transparent to-transparent"></div>

    <div class="mx-auto max-w-6xl px-4 pt-10 sm:pt-14">
      <h1 class="text-3xl font-extrabold leading-tight text-gray-900 dark:text-gray-100 sm:text-4xl">{{ $title }}</h1>
      @if($builder->team)
        <div class="mt-1 text-sm text-gray-600 dark:text-gray-300">
          Team: <span class="font-medium">{{ $builder->team }}</span>
        </div>
      @endif
    </div>
  </section>

  <section class="mx-auto max-w-6xl grid grid-cols