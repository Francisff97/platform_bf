{{-- resources/views/public/packs/show.blade.php --}}
<x-app-layout>
  @php
    ($seoSubject = $pack);
    $catColor = $pack->category->color ?? 'indigo';

    $titleColorMap = [
      'indigo'  => 'text-indigo-600 dark:text-indigo-300',
      'emerald' => 'text-emerald-600 dark:text-emerald-300',
      'amber'   => 'text-amber-600 dark:text-amber-300',
      'rose'    => 'text-rose-600 dark:text-rose-300',
      'sky'     => 'text-sky-600 dark:text-sky-300',
    ];
    $titleColor = $titleColorMap[$catColor] ?? 'text-gray-900 dark:text-gray-100';

    $badgeLight = [
      'indigo'=>'bg-indigo-50 text-indigo-700',
      'emerald'=>'bg-emerald-50 text-emerald-700',
      'amber'=>'bg-amber-50 text-amber-700',
      'rose'=>'bg-rose-50 text-rose-700',
      'sky'=>'bg-sky-50 text-sky-700',
    ];
    $badgeDark = [
      'indigo'=>'dark:bg-indigo-900 dark:text-indigo-100',
      'emerald'=>'dark:bg-emerald-900 dark:text-emerald-100',
      'amber'=>'dark:bg-amber-900 dark:text-amber-100',
      'rose'=>'dark:bg-rose-900 dark:text-rose-100',
      'sky'=>'dark:bg-sky-900 dark:text-sky-100',
    ];
    $badgeCls = ($badgeLight[$catColor] ?? 'bg-gray-100 text-gray-700').' '.($badgeDark[$catColor] ?? 'dark:bg-gray-800 dark:text-gray-100');
  @endphp

  {{-- HERO --}}
  <section class="relative isolate">
    <div class="pointer-events-none absolute inset-0 -z-10 bg-gradient-to-b from-[var(--accent)]/15 via-transparent to-transparent"></div>

    <div class="mx-auto max-w-6xl px-4 pt-14 sm:pt-20">
      @if($pack->category)
        <span class="mb-2 inline-block rounded-full px-2.5 py-0.5 text-xs font-medium {{ $badgeCls }}">
          {{ $pack->category->name }}
        </span>
      @endif

      <h1 class="text-3xl font-extrabold leading-tight {{ $titleColor }} sm:text-4xl">
        {{ $pack->title }}
      </h1>

      <div class="mt-1 text-sm text-gray-600 dark:text-gray-300">
        @if($pack->builder) by <span class="font-medium">{{ $pack->builder->name }}</span> @endif
      </div>
    </div>
  </section>

  {{-- COVER --}}
  @if($pack->image_path)
    <div class="mx-auto max-w-6xl px-4 pt-6">
      <div class="overflow-hidden rounded-2xl ring-1 ring-black/5 dark:ring-white/10">
        <x-img :src="Storage::url($pack->image_path)" class="max-h-[420px] w-full object-cover" />
      </div>
    </div>
  @endif

  {{-- BODY + BUYBOX --}}
  <div class="mx-auto grid max-w-6xl grid-cols-1 gap-8 px-4 py-10 md:grid-cols-3">
    <section class="md:col-span-2">
      @if($pack->excerpt)
        <p class="mb-4 text-lg text-gray-700 dark:text-gray-200">{{ $pack->excerpt }}</p>
      @endif

      <div class="rounded-2xl border border-gray-100 bg-white/70 p-6 shadow-sm backdrop-blur dark:border-gray-800 dark:bg-gray-900/60">
        <div class="prose max-w-none prose-p:leading-relaxed dark:prose-invert">
          {!! nl2br(e($pack->description)) !!}
        </div>
      </div>
    </section>

    <aside class="md:col-span-1">
      <div class="md:sticky md:top-24">
        <div class="overflow-hidden rounded-2xl border border-[color:var(--accent)]/30 bg-white/80 p-5 shadow-sm backdrop-blur dark:border-[color:var(--accent)]/30 dark:bg-gray-900/60">
          <div class="text-xs uppercase text-gray-500">Price</div>
          <div class="mt-1 text-2xl font-extrabold text-gray-900 dark:text-white">
            @money($pack->price_cents, $pack->currency)
          </div>

          <form method="POST" action="{{ route('cart.add.pack',$pack) }}" class="mt-5">
            @csrf
            <button class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-[var(--accent)] px-4 py-2.5 text-white shadow transition hover:opacity-90 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white/60">
              <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M3 3h2l.4 2M7 13h10l3-6H6.4M7 13l-2 7h14M10 21a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z"/></svg>
              Add to cart
            </button>
          </form>

          <ul class="mt-5 space-y-2 text-sm text-gray-600 dark:text-gray-300">
            <li class="flex items-center gap-2"><span class="inline-block h-1.5 w-1.5 rounded-full bg-[var(--accent)]"></span>We contact you after payment</li>
            <li class="flex items-center gap-2"><span class="inline-block h-1.5 w-1.5 rounded-full bg-[var(--accent)]"></span>Secure checkout via PayPal</li>
          </ul>
        </div>
      </div>
    </aside>
  </div>

  {{-- ====== VIDEO / TUTORIALS: mostra solo se add-on attivo (FeatureFlags) ====== --}}
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
  {{-- ====== COSTRUISCO L’ELENCO VIDEO (come prima) ====== --}}
  @php
    $isBuyer = auth()->check() && method_exists(auth()->user(),'hasPurchasedPack')
      ? auth()->user()->hasPurchasedPack($pack->id)
      : false;

    // tutorial visibili (buyer = tutti; altri = solo public)
    $tutorials = $isBuyer
      ? $pack->tutorials()->orderBy('sort_order')->get()
      : $pack->tutorials()->where('is_public', true)->orderBy('sort_order')->get();

    $videos = collect();

    // Fallback “Overview” dal Pack (prima card)
    $fallbackUrl = $isBuyer ? ($pack->private_video_url ?? $pack->video_url) : $pack->video_url;
    if (!empty($fallbackUrl)) {
      $embed = \App\Support\VideoEmbed::from($fallbackUrl);
      if ($embed) {
        $vis = ($isBuyer && $pack->private_video_url && $fallbackUrl === $pack->private_video_url) ? 'private' : 'public';
        $videos->push(['title'=>'Overview', 'embed'=>$embed, 'visibility'=>$vis]);
      }
    }

    // Aggiungo i tutorial collegati
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

    // deduplica per embed
    $videos   = $videos->unique('embed')->values();
    $featured = $videos->first();
    $others   = $videos->slice(1);
  @endphp

  {{-- ====== PLAYER GRANDE (come quello che avevi) ====== --}}
  @if($featured)
    <div class="mx-auto max-w-6xl px-4 pt-6">
      {{-- wrapper senza overflow per non “tagliare” eventuali modali esterni --}}
      <div
        x-data="videoPlayer('{{ $featured['embed'] }}')"
        x-init="init()"
        class="relative rounded-2xl ring-1 ring-black/5 dark:ring-white/10 bg-white/60 dark:bg-gray-900/60">

        {{-- aspect 16:9 con overflow solo qui (bordi arrotondati) --}}
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

          {{-- Placeholder consenso --}}
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

        {{-- footer: titolo + chip (come prima) --}}
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

  {{-- ====== MORE VIDEOS (uguale allo stile che avevi) ====== --}}
  @if($others->count())
    <div class="mx-auto max-w-6xl px-4 pb-10 pt-6">
      <div class="mb-2 flex items-center justify-between">
        <h3 class="text-lg font-semibold">More videos</h3>
        <div class="text-xs text-gray-500 sm:hidden">Swipe to watch more →</div>
      </div>

      {{-- MOBILE: slider 1-card --}}
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

      {{-- DESKTOP: grid 3 --}}
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

  {{-- ====== JS player (consent-aware) — identico, ma più robusto nel ready ====== --}}
  <script>
    function videoPlayer(src){
      return {
        src, canPlay: false, _try: 0, _max: 30,
        init(){
          const wait = () => {
            const apiReady = !!(window._iub && _iub.cs && _iub.cs.api);
            if (apiReady || this._try >= this._max) {
              this.evaluate(); this.bind();
              if (this.canPlay) this.$nextTick(()=>this.attach());
              return;
            }
            this._try++; setTimeout(wait,100);
          };
          wait();
        },
        evaluate(){
          try{
            if (window._iub && _iub.cs && _iub.cs.api) {
              const ok =
                (_iub.cs.api.getConsentFor && (_iub.cs.api.getConsentFor('experience') || _iub.cs.api.getConsentFor('marketing'))) ||
                (_iub.cs.api.getConsentForPurpose && (_iub.cs.api.getConsentForPurpose(3) || _iub.cs.api.getConsentForPurpose(4)));
              this.canPlay = !!ok;
            } else { this.canPlay = true; }
          }catch(e){ this.canPlay = true; }
        },
        bind(){
          const reload = () => { this.evaluate(); if (this.canPlay) this.attach(); };
          ['iubenda_consent_given','iubenda_consent_updated','iubenda_preferences_updated','iubenda_updated']
            .forEach(ev => document.addEventListener(ev, reload));
        },
        attach(){ if (this.$refs.frame && !this.$refs.frame.src) this.$refs.frame.src = this.src; },
        openPrefs(){ try { _iub.cs.api.openPreferences(); } catch(e){} },
        tryLoadAnyway(){ this.evaluate(); if (this.canPlay) this.attach(); }
      }
    }
  </script>
@endif
</x-app-layout>
