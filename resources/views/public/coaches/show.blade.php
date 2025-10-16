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
            <x-img :src="$img ?? $coach->image_url" class="h-64 w-full object-cover sm:h-72" />
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

        {{-- ====== VIDEO / TUTORIALS (Coach) — identico ai packs ====== --}}
@php
  $tutorialsEnabled = false;
  if (class_exists(\App\Support\FeatureFlags::class)) {
    $FF = \App\Support\FeatureFlags::class;
    foreach (['tutorials','addons.tutorials','video_tutorials'] as $key) {
      if (
        (method_exists($FF,'enabled')   && $FF::enabled($key)) ||
        (method_exists($FF,'isEnabled') && $FF::isEnabled($key)) ||
        (method_exists($FF,'on')        && $FF::on($key)) ||
        (method_exists($FF,'get')       && $FF::get($key))
      ) { $tutorialsEnabled = true; break; }
    }
  }
@endphp

@if($tutorialsEnabled)
  @php
    // L’utente ha acquistato questo coach?
    $isBuyer = auth()->check() && method_exists(auth()->user(),'hasPurchasedCoach')
      ? auth()->user()->hasPurchasedCoach($coach->id)
      : false;

    // Carico i tutorial: buyer -> tutti; non buyer -> solo pubblici
    $tutorials = $isBuyer
      ? $coach->tutorials()->orderBy('sort_order')->get()
      : $coach->tutorials()->where('is_public', true)->orderBy('sort_order')->get();

    $videos = collect();

    // Fallback “Overview” (es. campi video_url / private_video_url sul Coach)
    $fallbackUrl = $isBuyer ? ($coach->private_video_url ?? $coach->video_url) : $coach->video_url;
    if (!empty($fallbackUrl)) {
      if ($embed = \App\Support\VideoEmbed::from($fallbackUrl)) {
        $vis = ($isBuyer && $coach->private_video_url && $fallbackUrl === $coach->private_video_url) ? 'private' : 'public';
        $videos->push(['title'=>'Overview', 'embed'=>$embed, 'visibility'=>$vis]);
      }
    }

    // Tutorial collegati
    foreach ($tutorials as $t) {
      if (!$t->video_url) continue;
      if ($embed = \App\Support\VideoEmbed::from($t->video_url)) {
        $videos->push([
          'title'      => $t->title ?: 'Tutorial',
          'embed'      => $embed,
          'visibility' => $t->is_public ? 'public' : 'private',
        ]);
      }
    }

    // dedup su embed, split featured/others
    $videos   = $videos->unique('embed')->values();
    $featured = $videos->first();
    $others   = $videos->slice(1);
  @endphp

  {{-- ====== PLAYER GRANDE ====== --}}
  @if($featured)
    <div class="mx-auto max-w-6xl px-4 pt-6">
      <div x-data="videoPlayer('{{ $featured['embed'] }}')" class="relative rounded-2xl ring-1 ring-black/5 dark:ring-white/10 bg-white/60 dark:bg-gray-900/60">
        <div class="relative w-full overflow-hidden rounded-2xl" style="padding-top:56.25%">
          <iframe
            x-ref="frame"
            title="Featured video"
            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
            allowfullscreen
            loading="lazy"
            style="position:absolute;inset:0;width:100%;height:100%;border:0;display:block"
            x-show="loaded"
            x-cloak
          ></iframe>

          {{-- Cover + Play --}}
          <button type="button"
                  x-show="!loaded" x-cloak
                  @click="play()"
                  class="absolute inset-0 grid place-items-center"
                  :style="`background:#0b0d12 url('${thumb()}') center/cover no-repeat`">
            <span class="inline-flex h-14 w-14 items-center justify-center rounded-full bg-white/90 shadow">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-gray-900" viewBox="0 0 24 24" fill="currentColor"><path d="M8 5v14l11-7z"/></svg>
            </span>
          </button>
        </div>

        <div class="mt-2 flex items-center justify-between gap-3 px-1 pb-1 text-sm">
          <div class="px-2 font-medium truncate">{{ $featured['title'] }}</div>
          @php $visF = $featured['visibility'] ?? 'public'; @endphp
          <span class="mr-2 inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[10px] font-semibold
                       {{ $visF==='public'
                          ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-200'
                          : 'bg-rose-50 text-rose-700 dark:bg-rose-900/40 dark:text-rose-200' }}">
            {{ $visF==='public' ? 'PUBLIC' : 'PRIVATE' }}
          </span>
        </div>
      </div>
    </div>
  @endif

  {{-- ====== MORE VIDEOS ====== --}}
  @if($others->count())
    <div class="mx-auto max-w-6xl px-4 pb-10 pt-6">
      <div class="mb-2 flex items-center justify-between">
        <h3 class="text-lg font-semibold">More videos</h3>
        <div class="text-xs text-gray-500 sm:hidden">Swipe to watch more →</div>
      </div>

      {{-- Mobile slider --}}
      <div class="sm:hidden relative">
        <div class="pointer-events-none absolute right-0 top-0 h-full w-10 bg-gradient-to-l from-[var(--bg-light)]/90 to-transparent dark:from-[var(--bg-dark)]/90"></div>
        <div class="-mx-4 overflow-x-auto px-4">
          <div class="flex snap-x snap-mandatory gap-4">
            @foreach($others as $i => $v)
              <div class="w-[85%] shrink-0 snap-start">
                <div x-data="videoPlayer('{{ $v['embed'] }}')"
                     class="relative overflow-hidden rounded-2xl border border-gray-100 bg-white/70 shadow-sm ring-1 ring-black/5 backdrop-blur
                            dark:border-gray-800 dark:bg-gray-900/60 dark:ring-white/10">
                  <div class="relative w-full overflow-hidden rounded-t-2xl" style="padding-top:56.25%">
                    <iframe x-ref="frame" title="Video {{ $i+1 }}"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                            allowfullscreen loading="lazy"
                            style="position:absolute;inset:0;width:100%;height:100%;border:0;display:block"
                            x-show="loaded" x-cloak></iframe>

                    <button type="button" x-show="!loaded" x-cloak @click="play()"
                            class="absolute inset-0 grid place-items-center"
                            :style="`background:#0b0d12 url('${thumb()}') center/cover no-repeat`">
                      <span class="inline-flex h-12 w-12 items-center justify-center rounded-full bg-white/90 shadow">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-900" viewBox="0 0 24 24" fill="currentColor"><path d="M8 5v14l11-7z"/></svg>
                      </span>
                    </button>
                  </div>

                  <div class="flex items-center justify-between gap-3 border-t border-black/5 px-3 py-2 text-sm dark:border-white/10">
                    <div class="font-medium truncate">{{ $v['title'] }}</div>
                    @php $vis = $v['visibility'] ?? 'public'; @endphp
                    <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[10px] font-semibold
                                 {{ $vis==='public'
                                    ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-200'
                                    : 'bg-rose-50 text-rose-700 dark:bg-rose-900/40 dark:text-rose-200' }}">
                      {{ $vis==='public' ? 'PUBLIC' : 'PRIVATE' }}
                    </span>
                  </div>
                </div>
              </div>
            @endforeach
          </div>
        </div>
      </div>

      {{-- Desktop grid --}}
      <div class="hidden sm:grid grid-cols-3 gap-6">
        @foreach($others as $i => $v)
          <div x-data="videoPlayer('{{ $v['embed'] }}')"
               class="relative overflow-hidden rounded-2xl border border-gray-100 bg-white/70 shadow-sm ring-1 ring-black/5 backdrop-blur
                      dark:border-gray-800 dark:bg-gray-900/60 dark:ring-white/10">
            <div class="relative w-full overflow-hidden rounded-t-2xl" style="padding-top:56.25%">
              <iframe x-ref="frame" title="Video {{ $i+1 }}"
                      allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                      allowfullscreen loading="lazy"
                      style="position:absolute;inset:0;width:100%;height:100%;border:0;display:block"
                      x-show="loaded" x-cloak></iframe>

              <button type="button" x-show="!loaded" x-cloak @click="play()"
                      class="absolute inset-0 grid place-items-center"
                      :style="`background:#0b0d12 url('${thumb()}') center/cover no-repeat`">
                <span class="inline-flex h-12 w-12 items-center justify-center rounded-full bg-white/90 shadow">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-900" viewBox="0 0 24 24" fill="currentColor"><path d="M8 5v14l11-7z"/></svg>
                </span>
              </button>
            </div>
            <div class="flex items-center justify-between gap-3 border-t border-black/5 px-3 py-2 text-sm dark:border-white/10">
              <div class="font-medium truncate">{{ $v['title'] }}</div>
              @php $vis = $v['visibility'] ?? 'public'; @endphp
              <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[10px] font-semibold
                           {{ $vis==='public'
                              ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-200'
                              : 'bg-rose-50 text-rose-700 dark:bg-rose-900/40 dark:text-rose-200' }}">
                {{ $vis==='public' ? 'PUBLIC' : 'PRIVATE' }}
              </span>
            </div>
          </div>
        @endforeach
      </div>
    </div>
  @endif
@endif
</section>

{{-- ==== Alpine helper: identico ai packs ==== --}}
<script>
  function videoPlayer(src){
    return {
      src,
      loaded: false,
      thumb(){
        try {
          const s = this.src || '';
          let m = s.match(/\/embed\/([A-Za-z0-9_-]{6,})/);
          if (!m) m = s.match(/[?&]v=([A-Za-z0-9_-]{6,})/);
          if (!m) m = s.match(/youtu\.be\/([A-Za-z0-9_-]{6,})/);
          const id = m ? m[1] : null;
          return id ? `https://i.ytimg.com/vi/${id}/hqdefault.jpg` : '';
        } catch(e){ return ''; }
      },
      play(){
        if (this.loaded || !this.$refs.frame) return;
        const sep = this.src.includes('?') ? '&' : '?';
        this.$refs.frame.src = `${this.src}${sep}autoplay=1&rel=0`;
        this.loaded = true;
      }
    }
  }
</script>
</x-app-layout>
