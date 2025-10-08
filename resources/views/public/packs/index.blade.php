<x-app-layout>
  <x-auto-hero />

  @php
    // colore derivato dalla categoria selezionata (se presente)
    $selectedCat = $categories->firstWhere('slug', $cat ?? null);
    $tone = $selectedCat->color ?? null; // es. 'indigo' / 'emerald' / HEX non gestito qui

    $toneMap = [
      'indigo'  => ['chip'=>'bg-indigo-50 text-indigo-700 dark:bg-indigo-900 dark:text-indigo-100',
                    'chipOutline'=>'border-indigo-200 dark:border-indigo-800',
                    'focus'=>'focus:ring-indigo-500/20'],
      'emerald' => ['chip'=>'bg-emerald-50 text-emerald-700 dark:bg-emerald-900 dark:text-emerald-100',
                    'chipOutline'=>'border-emerald-200 dark:border-emerald-800',
                    'focus'=>'focus:ring-emerald-500/20'],
      'rose'    => ['chip'=>'bg-rose-50 text-rose-700 dark:bg-rose-900 dark:text-rose-100',
                    'chipOutline'=>'border-rose-200 dark:border-rose-800',
                    'focus'=>'focus:ring-rose-500/20'],
      'amber'   => ['chip'=>'bg-amber-50 text-amber-700 dark:bg-amber-900 dark:text-amber-100',
                    'chipOutline'=>'border-amber-200 dark:border-amber-800',
                    'focus'=>'focus:ring-amber-500/20'],
      'sky'     => ['chip'=>'bg-sky-50 text-sky-700 dark:bg-sky-900 dark:text-sky-100',
                    'chipOutline'=>'border-sky-200 dark:border-sky-800',
                    'focus'=>'focus:ring-sky-500/20'],
      'violet'  => ['chip'=>'bg-violet-50 text-violet-700 dark:bg-violet-900 dark:text-violet-100',
                    'chipOutline'=>'border-violet-200 dark:border-violet-800',
                    'focus'=>'focus:ring-violet-500/20'],
      'slate'   => ['chip'=>'bg-slate-50 text-slate-700 dark:bg-slate-900 dark:text-slate-100',
                    'chipOutline'=>'border-slate-200 dark:border-slate-800',
                    'focus'=>'focus:ring-slate-500/20'],
      'teal'    => ['chip'=>'bg-teal-50 text-teal-700 dark:bg-teal-900 dark:text-teal-100',
                    'chipOutline'=>'border-teal-200 dark:border-teal-800',
                    'focus'=>'focus:ring-teal-500/20'],
    ];

    $toneCfg = $toneMap[$tone] ?? [
      'chip' => 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-100',
      'chipOutline' => 'border-gray-200 dark:border-gray-700',
      'focus' => 'focus:ring-indigo-500/20',
    ];
  @endphp

  {{-- FILTER BAR – sticky + blur + tone aware --}}
  <div x-data="{ open: true }" class="sticky top-2 z-30 mt-[100px] mb-10">
    <div class="rounded-2xl border border-gray-200/60 bg-white/70 p-2 shadow-sm backdrop-blur
                dark:border-gray-700 dark:bg-gray-900/60">

      {{-- header mobile --}}
      <div class="flex items-center justify-between gap-3 px-2 sm:hidden">
        <div class="text-sm font-medium text-gray-700 dark:text-gray-200">Filters</div>
        <button type="button"
                @click="open=!open"
                class="inline-flex items-center gap-2 rounded-lg border px-3 py-1.5 text-sm
                       hover:bg-gray-50 {{ $toneCfg['chipOutline'] }} dark:hover:bg-gray-800">
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

        {{-- search --}}
        <label class="group relative sm:col-span-4">
          <input type="text" name="q" value="{{ $q ?? '' }}"
                 placeholder="Search packs, keywords…"
                 class="w-full rounded-full border border-gray-200 bg-white px-10 py-2.5 text-sm
                        outline-none transition placeholder:text-gray-400
                        {{ $toneCfg['focus'] }}
                        dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100" />
          <svg class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400"
               viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
        </label>

        {{-- category --}}
        <label class="relative sm:col-span-3">
          <select name="category"
                  class="w-full appearance-none rounded-full border bg-white px-4 py-2.5 pr-9 text-sm
                         border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 {{ $toneCfg['focus'] }}">
            <option value="">All categories</option>
            @foreach($categories as $c)
              <option value="{{ $c->slug }}" @selected(($cat ?? '')===$c->slug)>{{ $c->name }}</option>
            @endforeach
          </select>
          <svg class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400"
               viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="m6 9 6 6 6-6"/></svg>
        </label>

        {{-- builder --}}
        <label class="relative sm:col-span-3">
          <select name="builder"
                  class="w-full appearance-none rounded-full border bg-white px-4 py-2.5 pr-9 text-sm
                         border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 {{ $toneCfg['focus'] }}">
            <option value="">All builders</option>
            @foreach($builders as $b)
              <option value="{{ $b->slug }}" @selected(($builder ?? '')==$b->slug)>{{ $b->name }}</option>
            @endforeach
          </select>
          <svg class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400"
               viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="m6 9 6 6 6-6"/></svg>
        </label>

        {{-- sort --}}
        <label class="relative sm:col-span-2">
          <select name="sort"
                  class="w-full appearance-none rounded-full border bg-white px-4 py-2.5 pr-9 text-sm
                         border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 {{ $toneCfg['focus'] }}">
            <option value="latest" @selected(($sort ?? 'latest')==='latest')>Latest</option>
            <option value="price_asc" @selected(($sort ?? '')==='price_asc')>Price ↑</option>
            <option value="price_desc" @selected(($sort ?? '')==='price_desc')>Price ↓</option>
          </select>
          <svg class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400"
               viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="m6 9 6 6 6-6"/></svg>
        </label>

        {{-- actions + ACTIVE CHIPS (tone + scroll) --}}
        <div class="flex flex-col gap-2 sm:col-span-12">
          <div class="flex items-center gap-2">
            <button class="inline-flex items-center gap-2 rounded-full bg-[var(--accent)] px-4 py-2 text-sm font-semibold text-white hover:opacity-90">
              <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M3 6h18M6 12h12M10 18h4"/></svg>
              Apply
            </button>

            @if(request('q') || request('category') || request('builder') || request('sort'))
              <a href="{{ route('packs.public') }}"
                 class="inline-flex items-center gap-2 rounded-full border px-4 py-2 text-sm
                        hover:bg-gray-50 {{ $toneCfg['chipOutline'] }} dark:hover:bg-gray-800">
                Reset
              </a>
            @endif
          </div>

          {{-- CHIPS SCROLL --}}
          <div class="relative -mx-2">
            <div class="no-scrollbar flex gap-2 overflow-x-auto px-2 py-1.5 [scroll-snap-type:x_mandatory] [mask-image:linear-gradient(to_right,transparent,black_24px,black_calc(100%-24px),transparent)]">
              @if($q ?? false)
                <a href="{{ request()->fullUrlWithQuery(['q'=>null]) }}"
                   class="group inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-medium {{ $toneCfg['chip'] }} scroll-snap-align-start">
                  “{{ \Illuminate\Support\Str::limit($q,20) }}”
                  <span class="rounded-full bg-black/10 px-1.5 py-0.5 text-[10px] group-hover:bg-black/20 dark:bg-white/10 dark:group-hover:bg-white/20">×</span>
                </a>
              @endif
              @if($cat ?? false)
                <a href="{{ request()->fullUrlWithQuery(['category'=>null]) }}"
                   class="group inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-medium {{ $toneCfg['chip'] }} scroll-snap-align-start">
                  Category: {{ optional($categories->firstWhere('slug',$cat))->name ?? $cat }}
                  <span class="rounded-full bg-black/10 px-1.5 py-0.5 text-[10px] group-hover:bg-black/20 dark:bg-white/10 dark:group-hover:bg-white/20">×</span>
                </a>
              @endif
              @if($builder ?? false)
                <a href="{{ request()->fullUrlWithQuery(['builder'=>null]) }}"
                   class="group inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-medium {{ $toneCfg['chip'] }} scroll-snap-align-start">
                  Builder: {{ optional($builders->firstWhere('slug',$builder))->name ?? $builder }}
                  <span class="rounded-full bg-black/10 px-1.5 py-0.5 text-[10px] group-hover:bg-black/20 dark:bg-white/10 dark:group-hover:bg-white/20">×</span>
                </a>
              @endif
              @if(($sort ?? '') && ($sort!=='latest'))
                <a href="{{ request()->fullUrlWithQuery(['sort'=>null]) }}"
                   class="group inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-medium {{ $toneCfg['chip'] }} scroll-snap-align-start">
                  Sort: {{ str_replace('_',' ', $sort) }}
                  <span class="rounded-full bg-black/10 px-1.5 py-0.5 text-[10px] group-hover:bg-black/20 dark:bg-white/10 dark:group-hover:bg-white/20">×</span>
                </a>
              @endif
            </div>
          </div>
        </div>
      </form>
    </div>
  </div>

  {{-- GRID raggruppata per categoria --}}
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

        // builders unici nel gruppo → chip scrollabili sotto al titolo
        $buildersInGroup = $items->pluck('builder')->filter()->unique('id')->values();
      @endphp

      <div class="mb-4 mt-[50px]">
        <h2 class="font-orbitron text-2xl">
          <span class="rounded-full px-3 py-1 text-sm font-semibold {{ $catBadgeClass }}"
                @if($catBadgeStyle) style="{{ $catBadgeStyle }}" @endif>
            {{ $catName }}
          </span>
        </h2>

        {{-- SCROLL builders/tag del gruppo (stessa tinta della categoria) --}}
        @if($buildersInGroup->count())
          <div class="relative -mx-1 mt-2">
            <div class="no-scrollbar flex gap-2 overflow-x-auto px-1 py-1 [scroll-snap-type:x_mandatory] [mask-image:linear-gradient(to_right,transparent,black_24px,black_calc(100%-24px),transparent)]">
              @foreach($buildersInGroup as $b)
                <span class="scroll-snap-align-start inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium {{ $catBadgeClass }}"
                      @if($catBadgeStyle) style="{{ $catBadgeStyle }}" @endif>
                  {{ $b->name }}
                </span>
              @endforeach
            </div>
          </div>
        @endif
      </div>

      <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
        @foreach($items as $pack)
          <x-pack-card :pack="$pack" />
        @endforeach
      </div>

      <div class="h-8"></div>
    @endforeach

    <div class="mt-8">
      {{ $packs->links() }}
    </div>
  @else
    {{-- EMPTY STATE --}}
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
</x-app-layout>
