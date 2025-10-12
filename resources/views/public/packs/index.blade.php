<x-app-layout>
  <x-auto-hero/>

  {{-- FILTER BAR – identica alla tua, puoi tenerla o semplificarla --}}
  <div x-data="{ open: true }" class="sticky top-2 z-30 mt-[100px] mb-10">
    <div class="rounded-2xl border border-gray-200/60 bg-white/70 p-2 shadow-sm backdrop-blur
                dark:border-gray-700 dark:bg-gray-900/60">

      <div class="flex items-center justify-between gap-3 px-2 sm:hidden">
        <div class="text-sm font-medium text-gray-700 dark:text-gray-200">Filters</div>
        <button type="button"
                @click="open=!open"
                class="inline-flex items-center gap-2 rounded-lg border px-3 py-1.5 text-sm
                       hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800">
          <span x-show="!open">Show</span><span x-show="open">Hide</span>
          <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path x-show="!open" d="M4 6h16M7 12h10M10 18h4"/>
            <path x-show="open" d="m6 9 6 6 6-6"/>
          </svg>
        </button>
      </div>

      <form method="GET" action="{{ route('packs.public') }}"
            x-show="open" x-collapse
            class="grid grid-cols-1 gap-2 px-2 sm:grid-cols-12">

        <label class="group relative sm:col-span-4">
          <input type="text" name="q" value="{{ $q ?? '' }}"
                 placeholder="Search packs, keywords…"
                 class="w-full rounded-full border border-gray-200 bg-white px-10 py-2.5 text-sm
                        outline-none transition placeholder:text-gray-400
                        focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20
                        dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100" />
          <svg class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400"
               viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
        </label>

        <label class="relative sm:col-span-3">
          <select name="category"
                  class="w-full appearance-none rounded-full border border-gray-200 bg-white px-4 py-2.5 pr-9 text-sm
                         focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20
                         dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
            <option value="">All categories</option>
            @foreach($categories as $c)
              <option value="{{ $c->slug }}" @selected(($cat ?? '')===$c->slug)>{{ $c->name }}</option>
            @endforeach
          </select>
          <svg class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400"
               viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="m6 9 6 6 6-6"/></svg>
        </label>

        <label class="relative sm:col-span-3">
          <select name="builder"
                  class="w-full appearance-none rounded-full border border-gray-200 bg-white px-4 py-2.5 pr-9 text-sm
                         focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20
                         dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
            <option value="">All builders</option>
            @foreach($builders as $b)
              <option value="{{ $b->slug }}" @selected(($builder ?? '')==$b->slug)>{{ $b->name }}</option>
            @endforeach
          </select>
          <svg class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400"
               viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="m6 9 6 6 6-6"/></svg>
        </label>

        <label class="relative sm:col-span-2">
          <select name="sort"
                  class="w-full appearance-none rounded-full border border-gray-200 bg-white px-4 py-2.5 pr-9 text-sm
                         focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20
                         dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
            <option value="latest" @selected(($sort ?? 'latest')==='latest')>Latest</option>
            <option value="price_asc" @selected(($sort ?? '')==='price_asc')>Price ↑</option>
            <option value="price_desc" @selected(($sort ?? '')==='price_desc')>Price ↓</option>
          </select>
          <svg class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400"
               viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="m6 9 6 6 6-6"/></svg>
        </label>

        <div class="flex items-center gap-2 sm:col-span-12">
          <button class="inline-flex items-center gap-2 rounded-full bg-[var(--accent)] px-4 py-2 text-sm font-semibold text-white hover:opacity-90">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M3 6h18M6 12h12M10 18h4"/></svg>
            Apply
          </button>

          @if(request('q') || request('category') || request('builder') || request('sort'))
            <a href="{{ route('packs.public') }}"
               class="inline-flex items-center gap-2 rounded-full border px-4 py-2 text-sm
                      hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800">
              Reset
            </a>
          @endif
        </div>
      </form>
    </div>
  </div>

  {{-- === GRID per CATEGORIA → ora carosello orizzontale responsive === --}}
  @php
    $groups = $packs->groupBy(fn($p) => optional($p->category)->name ?? 'Uncategorized');

    $badgePalette = [
      'indigo'  => 'bg-indigo-50 text-indigo-700 dark:bg-indigo-900 dark:text-indigo-100',
      'emerald' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900 dark:text-emerald-100',
      'rose'    => 'bg-rose-50 text-rose-700 dark:bg-rose-900 dark:text-rose-100',
      'amber'   => 'bg-amber-50 text-amber-700 dark:bg-amber-900 dark:text-amber-100',
      'sky'     => 'bg-sky-50 text-sky-700 dark:bg-sky-900 dark:text-sky-100',
      'slate'   => 'bg-slate-50 text-slate-700 dark:bg-slate-900 dark:text-slate-100',
      'violet'  => 'bg-violet-50 text-violet-700 dark:bg-violet-900 dark:text-violet-100',
      'cyan'    => 'bg-cyan-50 text-cyan-700 dark:bg-cyan-900 dark:text-cyan-100',
      'pink'    => 'bg-pink-50 text-pink-700 dark:bg-pink-900 dark:text-pink-100',
      'lime'    => 'bg-lime-50 text-lime-700 dark:bg-lime-900 dark:text-lime-100',
      'teal'    => 'bg-teal-50 text-teal-700 dark:bg-teal-900 dark:text-teal-100',
    ];
  @endphp

  @if($packs->count())
    @foreach($groups as $catName => $items)
      @php
        $first = $items->first();
        $cat   = optional($first)->category;
        $color = $cat->color ?? 'indigo';

        $catBadgeClass = $badgePalette[$color] ?? 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-100';
        $catBadgeStyle = '';
        if (is_string($color) && preg_match('/^#/', $color)) {
          $catBadgeClass = 'text-white';
          $catBadgeStyle = "background: {$color}";
        }
      @endphp

      {{-- Titolo categoria --}}
      <h2 class="mb-3 mt-[50px] font-orbitron text-2xl">
        <span class="rounded-full px-3 py-1 text-sm font-semibold {{ $catBadgeClass }}"
              @if($catBadgeStyle) style="{{ $catBadgeStyle }}" @endif>
          {{ $catName }}
        </span>
      </h2>

      {{-- Carosello orizzontale: 1 mobile, 2 tablet, 3 desktop --}}
      <div class="relative -mx-1">
        <div
          class="no-scrollbar flex gap-4 overflow-x-auto px-1 py-1
                 [scroll-snap-type:x_mandatory]
                 [mask-image:linear-gradient(to_right,transparent,black_24px,black_calc(100%-24px),transparent)]">
          @foreach($items as $pack)
            <div class="snap-start shrink-0 basis-[90%] sm:basis-[48%] lg:basis-[31%]">
              <x-pack-card :pack="$pack" />
            </div>
          @endforeach
        </div>
      </div>

      <div class="h-8"></div>
    @endforeach

    <div class="mt-8">
      {{ $packs->links() }}
    </div>
  @else
    <div class="rounded-2xl border border-dashed border-gray-300 p-10 text-center dark:border-gray-700">
      <div class="mx-auto max-w-md">
        <h3 class="font-semibold text-gray-900 dark:text-gray-100">No packs found</h3>
        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
          Try changing filters or search terms.
        </p>
        <a href="{{ route('packs.public') }}"
           class="mt-4 inline-flex rounded-lg border px-4 py-2 text-sm hover:bg-gray-50 dark:hover:bg-gray-800">
          Reset
        </a>
      </div>
    </div>
  @endif

  {{-- Utilities per scrollbar nascosta --}}
  <style>
    .no-scrollbar::-webkit-scrollbar{ display:none; }
    .no-scrollbar{ -ms-overflow-style:none; scrollbar-width:none; }
  </style>
</x-app-layout>
