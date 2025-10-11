{{-- resources/views/public/coaches/show.blade.php --}}
<x-app-layout>
  @php
    ($seoSubject = $coach);
    $minPrice = optional($coach->prices->sortBy('price_cents')->first());
  @endphp

  {{-- HERO --}}
  <div class="relative isolate">
    <div class="pointer-events-none absolute inset-0 -z-10 bg-gradient-to-b from-[var(--accent)]/15 via-transparent to-transparent"></div>
    <div class="mx-auto max-w-6xl px-4 pt-16 sm:pt-20">
      <div class="flex flex-col gap-6 sm:flex-row sm:items-end sm:justify-between">
        <div>
          @if($coach->team)
            <span class="inline-flex items-center rounded-full bg-white/70 px-2.5 py-0.5 text-xs font-medium text-gray-700 ring-1 ring-black/5 backdrop-blur dark:bg-gray-900/70 dark:text-gray-100 dark:ring-white/10">
              {{ $coach->team }}
            </span>
          @endif
          <h1 class="mt-2 text-3xl font-extrabold tracking-tight text-gray-900 dark:text-white sm:text-4xl">
            {{ $coach->name }}
          </h1>
          @if($minPrice)
            <div class="mt-1 text-sm text-gray-600 dark:text-gray-300">
              From <span class="font-semibold">@money($minPrice->price_cents, $minPrice->currency)</span>
            </div>
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
    {{-- MEDIA/META --}}
    <aside class="md:col-span-1">
      <div class="overflow-hidden rounded-2xl border border-gray-100 bg-white/80 shadow-sm ring-1 ring-black/5 backdrop-blur dark:border-gray-800 dark:bg-gray-900/60 dark:ring-white/10">
        <div class="relative">
          @if($coach->image_path)
            <x-img :src="Storage::url($coach->image_path)" class="h-64 w-full object-cover sm:h-72" />
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

    {{-- DESCRIZIONE + BUY --}}
    <section class="md:col-span-2">
      <div class="rounded-2xl border border-gray-100 bg-white/70 p-6 shadow-sm backdrop-blur dark:border-gray-800 dark:bg-gray-900/60">
        @if(!empty($coach->description))
          <div class="prose max-w-none prose-p:leading-relaxed prose-headings:scroll-mt-24 dark:prose-invert">{!! nl2br(e($coach->description)) !!}</div>
        @else
          <p class="text-gray-600 dark:text-gray-300">Coach profile.</p>
        @endif
      </div>

      <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-5">
        <div class="lg:col-span-3"></div>
        <div class="lg:col-span-2">
          <div class="lg:sticky lg:top-24">
            <div class="rounded-2xl border border-[color:var(--accent)]/30 bg-white/80 p-5 shadow-sm backdrop-blur dark:border-[color:var(--accent)]/30 dark:bg-gray-900/60">
              @auth
                @if($coach->prices->count())
                  <form method="POST" action="{{ route('cart.add.coach', $coach) }}" class="space-y-3">
                    @csrf
                    <div>
                      <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-200">Select duration</label>
                      <select name="price_id" class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 outline-none transition focus:ring-2 focus:ring-[var(--accent)]/30 dark:border-gray-800 dark:bg-gray-950 dark:text-gray-100">
                        @foreach($coach->prices as $price)
                          <option value="{{ $price->id }}">{{ $price->duration }} — @money($price->price_cents, $price->currency)</option>
                        @endforeach
                      </select>
                    </div>
                    <button class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-[var(--accent)] px-4 py-2.5 text-white shadow transition hover:opacity-90 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white/60">
                      <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M3 3h2l.4 2M7 13h10l3-6H6.4M7 13l-2 7h14M10 21a1 1 0 1 0 0-2 1 1 0 0 0 0 2Zm8 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z"/></svg>
                      Add to cart
                    </button>
                  </form>
                @else
                  <div class="text-sm text-gray-600 dark:text-gray-300">No Prices available for this coach.</div>
                @endif
              @else
                <a href="{{ route('login') }}" class="inline-flex w-full items-center justify-center gap-2 rounded-xl border px-4 py-2.5 text-sm hover:bg-gray-50 dark:border-gray-800 dark:hover:bg-gray-800">
                  Login to buy coaching
                </a>
              @endauth
            </div>
          </div>
        </div>
      </div>

      {{-- ====== VIDEO / TUTORIALS (Coach) — visibili solo se add-on attivo ====== --}}
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

    // Elenco tutorial: buyer = tutti; non buyer = solo pubblici
    $tutorials = $isBuyer
      ? $coach->tutorials()->orderBy('sort_order')->get()
      : $coach->tutorials()->where('is_public', true)->orderBy('sort_order')->get();

    $videos = collect();

    // Fallback “Overview” presi dal coach (se usi i campi sul modello)
    $fallbackUrl = $isBuyer ? ($coach->private_video_url ?? $coach->video_url) : $coach->video_url;
    if (!empty($fallbackUrl)) {
      $embed = \App\Support\VideoEmbed::from($fallbackUrl);
      if ($embed) {
        $vis = ($isBuyer && $coach->private_video_url && $fallbackUrl === $coach->private_video_url) ? 'private' : 'public';
        $videos->push(['title'=>'Overview', 'embed'=>$embed, 'visibility'=>$vis]);
      }
    }

    // Tutorial collegati al coach
    foreach ($tutorials as $t) {
      if (!$t->video_url) continue;
      $embed = \App\Support\VideoEmbed::from($t->video_url);
      if ($embed) {
        $videos->push([
          'title'      => $t->title ?: 'Tutorial',
          'embed'      => $embed,
          'visibility' => $t->is_public ? 'public' : 'private',
        ]);
      }
    }

    // deduplica e split featured/others
    $videos   = $videos->unique('embed')->values();
    $featured = $videos->first();
    $others   = $videos->slice(1);
  @endphp

  {{-- ====== PLAYER GRANDE (come nei packs) ====== --}}
  @if($featured)
    <div class="mx-auto max-w-6xl px-4 pt-6">
      <div
        x-data="videoPlayer('{{ $featured['embed'] }}')"
        x-init="init()"
        class="relative rounded-2xl ring-1 ring-black/5 dark:ring-white/10 bg-white/60 dark:bg-gray-900/60">

        <div class="relative w-full overflow-hidden rounded-2xl" style="padding-top:56.25%">
          <iframe
            x-ref="frame"
            title="Featured video"
            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
            allowfullscreen
            loading="lazy"
            style="position:absolute;inset:0;width:100%;height:100%;border:0;display:block"
            x-show="canPlay" x-cloak
          ></iframe>

          <div x-show="!canPlay" x-cloak class="absolute inset-0 grid place-items-center p-4">
            <div class="w-full max-w-lg rounded-xl border border-[color:var(--accent)]/30 bg-white/80 p-5 text-center shadow-sm backdrop-blur
                        dark:border-[color:var(--accent)]/25 dark:bg-gray-900/70">
              <div class="mx-auto mb-3 grid h-12 w-12 place-items-center rounded-full bg-[color:var(--accent)]/10 text-[color:var(--accent)]">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="currentColor"><path d="M8 5v14l11-7z"/></svg>
              </div>
              <h3 class="text-base font-semibold">Enable video playback</h3>
              <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">
                To watch this video you need to allow <span class="font-medium">experience/marketing</span> cookies in the cookie preferences.
              </p>
              <div class="mt-4 flex flex-col items-center gap-2 sm:flex-row sm:justify-center">
                <button type="button" @click="openPrefs()"
                        class="w-full sm:w-auto rounded-lg bg-[color:var(--accent)] px-4 py-2 text-white shadow hover:opacity-90">Open cookie preferences</button>
                <button type="button" @click="tryLoadAnyway()"
                        class="w-full sm:w-auto rounded-lg border px-4 py-2 text-sm hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800">I already accepted</button>
              </div>
              <p class="mt-3 text-xs text-gray-500 dark:text-gray-400">Tip: if you don’t accept cookies you might not see embedded videos.</p>
            </div>
          </div>
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

  {{-- ====== MORE VIDEOS (slider mobile / grid 3 desktop) ====== --}}
  @if($others->count())
    <div class="mx-auto max-w-6xl px-4 pb-10 pt-6">
      <div class="mb-2 flex items-center justify-between">
        <h3 class="text-lg font-semibold">More videos</h3>
        <div class="text-xs text-gray-500 sm:hidden">Swipe to watch more →</div>
      </div>

      {{-- MOBILE --}}
      <div class="sm:hidden relative">
        <div class="pointer-events-none absolute right-0 top-0 h-full w-10 bg-gradient-to-l from-[var(--bg-light)]/90 to-transparent dark:from-[var(--bg-dark)]/90"></div>
        <div class="-mx-4 overflow-x-auto px-4">
          <div class="flex snap-x snap-mandatory gap-4">
            @foreach($others as $i => $v)
              <div class="w-[85%] shrink-0 snap-start">
                <div
                  x-data="videoPlayer('{{ $v['embed'] }}')"
                  x-init="init()"
                  class="relative overflow-hidden rounded-2xl border border-gray-100 bg-white/70 shadow-sm ring-1 ring-black/5 backdrop-blur
                         dark:border-gray-800 dark:bg-gray-900/60 dark:ring-white/10">
                  <div class="relative w-full overflow-hidden rounded-t-2xl" style="padding-top:56.25%">
                    <iframe x-ref="frame" title="Video {{ $i+1 }}"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                            allowfullscreen loading="lazy"
                            style="position:absolute;inset:0;width:100%;height:100%;border:0;display:block"
                            x-show="canPlay" x-cloak></iframe>
                    <div x-show="!canPlay" x-cloak class="absolute inset-0 grid place-items-center p-3">
                      <div class="w-full max-w-sm rounded-xl border border-[color:var(--accent)]/30 bg-white/85 p-4 text-center text-xs shadow-sm backdrop-blur
                                  dark:border-[color:var(--accent)]/25 dark:bg-gray-900/70">
                        Allow cookies to play this video.
                        <div class="mt-2 flex justify-center gap-2">
                          <button type="button" @click="openPrefs()" class="rounded bg-[color:var(--accent)] px-3 py-1.5 text-white">Preferences</button>
                          <button type="button" @click="tryLoadAnyway()" class="rounded border px-3 py-1.5 dark:border-gray-700">I accepted</button>
                        </div>
                      </div>
                    </div>
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

      {{-- DESKTOP --}}
      <div class="hidden sm:grid grid-cols-3 gap-6">
        @foreach($others as $i => $v)
          <div
            x-data="videoPlayer('{{ $v['embed'] }}')"
            x-init="init()"
            class="relative overflow-hidden rounded-2xl border border-gray-100 bg-white/70 shadow-sm ring-1 ring-black/5 backdrop-blur
                   dark:border-gray-800 dark:bg-gray-900/60 dark:ring-white/10">
            <div class="relative w-full overflow-hidden rounded-t-2xl" style="padding-top:56.25%">
              <iframe x-ref="frame" title="Video {{ $i+1 }}"
                      allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                      allowfullscreen loading="lazy"
                      style="position:absolute;inset:0;width:100%;height:100%;border:0;display:block"
                      x-show="canPlay" x-cloak></iframe>
              <div x-show="!canPlay" x-cloak class="absolute inset-0 grid place-items-center p-3">
                <div class="w-full max-w-sm rounded-xl border border-[color:var(--accent)]/30 bg-white/85 p-4 text-center text-xs shadow-sm backdrop-blur
                            dark:border-[color:var(--accent)]/25 dark:bg-gray-900/70">
                  Allow cookies to play this video.
                  <div class="mt-2 flex justify-center gap-2">
                    <button type="button" @click="openPrefs()" class="rounded bg-[color:var(--accent)] px-3 py-1.5 text-white">Preferences</button>
                    <button type="button" @click="tryLoadAnyway()" class="rounded border px-3 py-1.5 dark:border-gray-700">I accepted</button>
                  </div>
                </div>
              </div>
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

  {{-- ====== Alpine helper (stesso JS dei packs) ====== --}}
  <script>
    function videoPlayer(src){
      return {
        src, canPlay: false,
        init(){
          try {
            if (window._iub && _iub.cs && _iub.cs.api) {
              const ok =
                (_iub.cs.api.getConsentFor && (_iub.cs.api.getConsentFor('experience') || _iub.cs.api.getConsentFor('marketing'))) ||
                (_iub.cs.api.getConsentForPurpose && (_iub.cs.api.getConsentForPurpose(3) || _iub.cs.api.getConsentForPurpose(4)));
              this.canPlay = !!ok;
              document.addEventListener('iubenda_consent_given', () => { this.load(); }, { once:true });
              document.addEventListener('iubenda_updated',      () => { this.load(); });
            } else {
              this.canPlay = true;
            }
          } catch(e){ this.canPlay = true; }
          if (this.canPlay) this.$nextTick(() => this.attach());
        },
        attach(){ if (this.$refs.frame && !this.$refs.frame.src) this.$refs.frame.src = this.src; },
        load(){
          try{
            const ok =
              (window._iub && _iub.cs && _iub.cs.api)
                ? ((_iub.cs.api.getConsentFor && (_iub.cs.api.getConsentFor('experience') || _iub.cs.api.getConsentFor('marketing')))
                    || (_iub.cs.api.getConsentForPurpose && (_iub.cs.api.getConsentForPurpose(3) || _iub.cs.api.getConsentForPurpose(4))))
                : true;
            this.canPlay = !!ok;
            if (this.canPlay) this.attach();
          }catch(e){ this.canPlay = true; this.attach(); }
        },
        openPrefs(){ try{ _iub.cs.api.openPreferences(); }catch(e){} },
        tryLoadAnyway(){ this.load(); }
      }
    }
  </script>
@endif
                      
</x-app-layout>
