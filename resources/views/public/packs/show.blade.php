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
  // Usa il tuo toggler
  $tutorialsEnabled = false;
  if (class_exists(\App\Support\FeatureFlags::class)) {
      $FF = \App\Support\FeatureFlags::class;

      // Prova chiavi comuni per sicurezza
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

  {{-- ====== COSTRUISCO L’ELENCO VIDEO ====== --}}
  @php
    $isBuyer = auth()->check() && method_exists(auth()->user(),'hasPurchasedPack')
      ? auth()->user()->hasPurchasedPack($pack->id)
      : false;

    $tutorials = $isBuyer
      ? $pack->tutorials()->orderBy('sort_order')->get()
      : $pack->tutorials()->where('is_public', true)->orderBy('sort_order')->get();

    $videos = collect();

    // Fallback “Overview” dal Pack
    $fallbackUrl = $isBuyer ? ($pack->private_video_url ?? $pack->video_url) : $pack->video_url;
    if (!empty($fallbackUrl)) {
      $embed = \App\Support\VideoEmbed::from($fallbackUrl);
      if ($embed) {
        $vis = ($isBuyer && $pack->private_video_url && $fallbackUrl === $pack->private_video_url) ? 'private' : 'public';
        $videos->push(['title'=>'Overview', 'embed'=>$embed, 'visibility'=>$vis]);
      }
    }

    // Tutorial collegati
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

    // deduplica
    $videos = $videos->unique('embed')->values();
  @endphp

  @if($videos->count())
    <div class="mx-auto max-w-6xl px-4 pb-10 pt-6"
         x-data="videoRail(@json($videos))"
         x-init="init()">

      <div class="mb-3 flex items-center justify-between">
        <h3 class="text-lg font-semibold">More videos</h3>
        <div class="text-xs text-gray-500 sm:hidden">Swipe to watch more →</div>
      </div>

      {{-- Rail orizzontale: mobile 1 card, desktop 3 (50% + 25% + 25%) --}}
      <div class="relative">
        <div class="-mx-4 overflow-x-auto px-4" x-ref="scroller" @scroll.passive="onScroll">
          <div class="flex items-stretch gap-4 snap-x snap-mandatory" x-ref="track">
            @foreach($videos as $i => $v)
              <div class="snap-start shrink-0"
                   :style="itemWidth({{ $i }})"
                   x-ref="item{{ $i }}">
                <div
                  x-data="videoPlayer(@js($v['embed']))"
                  x-init="init()"
                  class="relative overflow-hidden rounded-2xl border border-gray-100 bg-white/70 shadow-sm ring-1 ring-black/5 backdrop-blur
                         dark:border-gray-800 dark:bg-gray-900/60 dark:ring-white/10">

                  {{-- 16:9 --}}
                  <div class="relative w-full" style="padding-top:56.25%">
                    <iframe x-ref="frame" title="Video {{ $i+1 }}"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                            allowfullscreen loading="lazy"
                            style="position:absolute;inset:0;width:100%;height:100%;border:0;display:block"
                            x-show="canPlay"></iframe>

                    {{-- Placeholder se mancano i consensi --}}
                    <div x-show="!canPlay" class="absolute inset-0 grid place-items-center p-3">
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

                  {{-- footer card: titolo + chip --}}
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

        {{-- bordo sfumato a destra per indicare scroll (solo mobile) --}}
        <div class="pointer-events-none absolute right-0 top-0 h-full w-10 bg-gradient-to-l from-[var(--bg-light)]/90 to-transparent dark:from-[var(--bg-dark)]/90 sm:hidden"></div>
      </div>
    </div>
  @endif

  {{-- JS: rail responsiva --}}
  <script>
    function videoRail(videos){
      return {
        videos,
        featuredIndex: 0,
        scroller: null,
        items: [],
        init(){
          this.scroller = this.$refs.scroller;
          this.items = Array.from({length: this.videos.length}, (_,i)=> this.$refs['item'+i]);
          this.updateFeatured();
          window.addEventListener('resize', () => this.updateFeatured(true), { passive: true });
        },
        itemWidth(i){
          const isDesktop = window.innerWidth >= 640; // sm
          if (!isDesktop) return 'width: 85%';
          return `width: ${this.featuredIndex === i ? '50%' : '25%'}`;
        },
        onScroll(){ this.updateFeatured(); },
        updateFeatured(force = false){
          const isDesktop = window.innerWidth >= 640;
          if (!isDesktop || !this.scroller) { this.featuredIndex = 0; return; }
          let best = 0, bestDelta = Infinity;
          const parentRect = this.scroller.getBoundingClientRect();
          this.items.forEach((el, idx) => {
            if (!el) return;
            const delta = Math.abs(el.getBoundingClientRect().left - parentRect.left);
            if (delta < bestDelta) { bestDelta = delta; best = idx; }
          });
          if (force || best !== this.featuredIndex) { this.featuredIndex = best; this.$nextTick(() => {}); }
        }
      }
    }
  </script>

  {{-- JS: player con consenso Iubenda --}}
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
