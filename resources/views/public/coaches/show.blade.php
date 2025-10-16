<x-app-layout>
  @php
    ($seoSubject = $coach);
    $minPrice = optional($coach->prices->sortBy('price_cents')->first());
  @endphp

  <div class="relative isolate">
    <div class="pointer-events-none absolute inset-0 -z-10 bg-gradient-to-b from-[var(--accent)]/15 via-transparent to-transparent"></div>
    <div class="mx-auto max-w-6xl px-4 pt-16 sm:pt-20">
      <div class="flex flex-col gap-6 sm:flex-row sm:items-end sm:justify-between">
        <div>
          @if($coach->team)
            <span class="inline-flex items-center rounded-full bg-white/70 px-2.5 py-0.5 text-xs font-medium text-gray-700 ring-1 ring-black/5 backdrop-blur dark:bg-gray-900/70 dark:text-gray-100 dark:ring-white/10">{{ $coach->team }}</span>
          @endif
          <h1 class="mt-2 text-3xl font-extrabold tracking-tight text-gray-900 dark:text-white sm:text-4xl">{{ $coach->name }}</h1>
          @if($minPrice)
            <div class="mt-1 text-sm text-gray-600 dark:text-gray-300">From <span class="font-semibold">@money($minPrice->price_cents, $minPrice->currency)</span></div>
          @endif
        </div>

        <div class="flex items-center gap-2">
          <button x-data @click="navigator.clipboard?.writeText(window.location.href)" class="rounded-lg border border-gray-200 bg-white/80 px-3 py-1.5 text-sm text-gray-700 shadow-sm backdrop-blur transition hover:bg-white dark:border-gray-800 dark:bg-gray-900/70 dark:text-gray-100">Copy link</button>
          <a href="{{ route('coaches.index') }}" class="rounded-lg border border-gray-200 bg-white/80 px-3 py-1.5 text-sm text-gray-700 shadow-sm backdrop-blur transition hover:bg-white dark:border-gray-800 dark:bg-gray-900/70 dark:text-gray-100">Back to coaches</a>
        </div>
      </div>
    </div>
  </div>

  <div class="mx-auto grid max-w-6xl grid-cols-1 gap-8 px-4 py-10 md:grid-cols-3">
    <aside class="md:col-span-1">
      <div class="overflow-hidden rounded-2xl border border-gray-100 bg-white/80 shadow-sm ring-1 ring-black/5 backdrop-blur dark:border-gray-800 dark:bg-gray-900/60 dark:ring-white/10">
        <div class="relative">
          @if($coach->image_url)
            <x-img :src="$coach->detailSrc() ?? $coach->image_url" class="h-64 w-full object-cover sm:h-72" />
          @else
            <div class="h-64 w-full bg-gray-200 dark:bg-gray-800"></div>
          @endif
          <div class="pointer-events-none absolute inset-x-0 bottom-0 h-16 bg-gradient-to-t from-black/40 to-transparent"></div>
        </div>

        <div class="space-y-4 p-5">
          <div class="grid grid-cols-2 gap-3 text-sm">
            <div class="rounded-xl border border-gray-200 bg-white/60 p-3 dark:border-gray-800 dark:bg-gray-900/60">
              <div class="text-[11px] uppercase text-gray-500">Team</div>
              <div class="truncate font-medium text-gray-900 dark:text-gray-100">{{ $coach->team ?? '—' }}</div>
            </div>
            <div class="rounded-xl border border-gray-200 bg-white/60 p-3 dark:border-gray-800 dark:bg-gray-900/60">
              <div class="text-[11px] uppercase text-gray-500">Sessions</div>
              <div class="truncate font-medium text-gray-900 dark:text-gray-100">{{ $coach->prices->count() ? $coach->prices->count().' options' : '—' }}</div>
            </div>
          </div>

          @if(!empty($coach->skills) && is_iterable($coach->skills))
            <div>
              <div class="mb-2 text-xs font-medium uppercase tracking-wide text-gray-500">Skills</div>
              <div class="flex flex-wrap gap-2">
                @foreach($coach->skills as $s)
                  <span class="rounded-full border border-gray-200 bg-white/60 px-2.5 py-1 text-[11px] font-medium text-gray-700 dark:border-gray-800 dark:bg-gray-900/60 dark:text-gray-200">{{ $s }}</span>
                @endforeach
              </div>
            </div>
          @endif
        </div>
      </div>
    </aside>

    <section class="md:col-span-2">
      <div class="rounded-2xl border border-gray-100 bg-white/70 p-6 shadow-sm backdrop-blur dark:border-gray-800 dark:bg-gray-900/60">
        @if(!empty($coach->description))
          <div class="prose max-w-none prose-p:leading-relaxed prose-headings:scroll-mt-24 dark:prose-invert">{!! nl2br(e($coach->description)) !!}</div>
        @else
          <p class="text-gray-600 dark:text-gray-300">Coach profile.</p>
        @endif
      </div>

      {{-- … (resto video identico al tuo) --}}
      @include('public.coaches.partials.videos')
    </section>
  </div>
</x-app-layout>