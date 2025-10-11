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

  {{-- HERO: titolo + badge + builder --}}
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
        @if($pack->builder)
          by <span class="font-medium">{{ $pack->builder->name }}</span>
        @endif
      </div>
    </div>
  </section>

  {{-- COVER / MEDIA --}}
  @if($pack->image_path)
    <div class="mx-auto max-w-6xl px-4 pt-6">
      <div class="overflow-hidden rounded-2xl ring-1 ring-black/5 dark:ring-white/10">
        <x-img :src="Storage::url($pack->image_path)"
               class="max-h-[420px] w-full object-cover" />
      </div>
    </div>
  @endif

  {{-- BODY: 2 colonne --}}
  <div class="mx-auto grid max-w-6xl grid-cols-1 gap-8 px-4 py-10 md:grid-cols-3">
    {{-- Colonna testo --}}
    <section class="md:col-span-2">
      @if($pack->excerpt)
        <p class="mb-4 text-lg text-gray-700 dark:text-gray-200">
          {{ $pack->excerpt }}
        </p>
      @endif

      <div class="rounded-2xl border border-gray-100 bg-white/70 p-6 shadow-sm backdrop-blur
                  dark:border-gray-800 dark:bg-gray-900/60">
        <div class="prose max-w-none prose-p:leading-relaxed dark:prose-invert">
          {!! nl2br(e($pack->description)) !!}
        </div>
      </div>
    </section>

    {{-- Buy Box (sticky) --}}
    <aside class="md:col-span-1">
      <div class="md:sticky md:top-24">
        <div class="overflow-hidden rounded-2xl border border-[color:var(--accent)]/30 bg-white/80 p-5 shadow-sm backdrop-blur
                    dark:border-[color:var(--accent)]/30 dark:bg-gray-900/60">
          <div class="text-xs uppercase text-gray-500">Price</div>
          <div class="mt-1 text-2xl font-extrabold text-gray-900 dark:text-white">
            @money($pack->price_cents, $pack->currency)
          </div>

          <form method="POST" action="{{ route('cart.add.pack',$pack) }}" class="mt-5">
            @csrf
            <button
              class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-[var(--accent)] px-4 py-2.5 text-white shadow
                     transition hover:opacity-90 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white/60">
              <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <path d="M3 3h2l.4 2M7 13h10l3-6H6.4M7 13l-2 7h14M10 21a1 1 0 1 0 0-2 1 1 0 0 0 0 2Zm8 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z"/>
              </svg>
              Add to cart
            </button>
          </form>

          {{-- info extra (opzionali) --}}
          <ul class="mt-5 space-y-2 text-sm text-gray-600 dark:text-gray-300">
            <li class="flex items-center gap-2">
              <span class="inline-block h-1.5 w-1.5 rounded-full bg-[var(--accent)]"></span>
              Instant download after purchase
            </li>
            <li class="flex items-center gap-2">
              <span class="inline-block h-1.5 w-1.5 rounded-full bg-[var(--accent)]"></span>
              Secure checkout via PayPal
            </li>
          </ul>
        </div>
      </div>
    </aside>
  </div>
 <x-app-layout>
    @php
    ($seoSubject = $coach);
    // utility per prezzo minimo (se serve evidenziarlo in header)
    $minPrice = optional($coach->prices->sortBy('price_cents')->first());
  @endphp

  {{-- HERO con gradient + glow --}}
  <div class="relative isolate">
    <div class="pointer-events-none absolute inset-0 -z-10 bg-gradient-to-b from-[var(--accent)]/15 via-transparent to-transparent"></div>
    <div class="mx-auto max-w-6xl px-4 pt-16 sm:pt-20">
      <div class="flex flex-col gap-6 sm:flex-row sm:items-end sm:justify-between">
        <div>
          @if($coach->team)
            <span class="inline-flex items-center rounded-full bg-white/70 px-2.5 py-0.5 text-xs font-medium text-gray-700 ring-1 ring-black/5 backdrop-blur
                          dark:bg-gray-900/70 dark:text-gray-100 dark:ring-white/10">
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

        {{-- azioni rapide --}}
        <div class="flex items-center gap-2">
          <button
            x-data
            @click="navigator.clipboard?.writeText(window.location.href)"
            class="rounded-lg border border-gray-200 bg-white/80 px-3 py-1.5 text-sm text-gray-700 shadow-sm backdrop-blur transition hover:bg-white
                   dark:border-gray-800 dark:bg-gray-900/70 dark:text-gray-100">
            Copy link
          </button>
          <a href="{{ route('coaches.index') }}"
             class="rounded-lg border border-gray-200 bg-white/80 px-3 py-1.5 text-sm text-gray-700 shadow-sm backdrop-blur transition hover:bg-white
                    dark:border-gray-800 dark:bg-gray-900/70 dark:text-gray-100">
            Back to coaches
          </a>
        </div>
      </div>
    </div>
  </div>

  {{-- BODY: layout 2 colonne --}}
  <div class="mx-auto grid max-w-6xl grid-cols-1 gap-8 px-4 py-10 md:grid-cols-3">

    {{-- Colonna sinistra: media + meta --}}
    <aside class="md:col-span-1">
      <div class="overflow-hidden rounded-2xl border border-gray-100 bg-white/80 shadow-sm ring-1 ring-black/5 backdrop-blur
                  dark:border-gray-800 dark:bg-gray-900/60 dark:ring-white/10">
        <div class="relative">
          @if($coach->image_path)
            <x-img :src="Storage::url($coach->image_path)" class="h-64 w-full object-cover sm:h-72" />
          @else
            <div class="h-64 w-full bg-gray-200 dark:bg-gray-800"></div>
          @endif

          {{-- glow accent in basso --}}
          <div class="pointer-events-none absolute inset-x-0 bottom-0 h-16 bg-gradient-to-t from-black/40 to-transparent"></div>
        </div>

        <div class="space-y-4 p-5">
          {{-- meta basilari --}}
          <div class="grid grid-cols-2 gap-3 text-sm">
            <div class="rounded-xl border border-gray-200 bg-white/60 p-3 dark:border-gray-800 dark:bg-gray-900/60">
              <div class="text-[11px] uppercase text-gray-500">Team</div>
              <div class="truncate font-medium text-gray-900 dark:text-gray-100">
                {{ $coach->team ?? '—' }}
              </div>
            </div>

            <div class="rounded-xl border border-gray-200 bg-white/60 p-3 dark:border-gray-800 dark:bg-gray-900/60">
              <div class="text-[11px] uppercase text-gray-500">Sessions</div>
              <div class="truncate font-medium text-gray-900 dark:text-gray-100">
                {{ $coach->prices->count() ? $coach->prices->count().' options' : '—' }}
              </div>
            </div>
          </div>

          {{-- skills / tags --}}
          @if(!empty($coach->skills) && is_iterable($coach->skills))
            <div>
              <div class="mb-2 text-xs font-medium uppercase tracking-wide text-gray-500">Skills</div>
              <div class="flex flex-wrap gap-2">
                @foreach($coach->skills as $s)
                  <span class="rounded-full border border-gray-200 bg-white/60 px-2.5 py-1 text-[11px] font-medium text-gray-700
                               dark:border-gray-800 dark:bg-gray-900/60 dark:text-gray-200">
                    {{ $s }}
                  </span>
                @endforeach
              </div>
            </div>
          @endif
        </div>
      </div>
    </aside>

    {{-- Colonna destra: testo + acquisto sticky --}}
    <section class="md:col-span-2">
      {{-- Descrizione / bio --}}
      <div class="rounded-2xl border border-gray-100 bg-white/70 p-6 shadow-sm backdrop-blur
                  dark:border-gray-800 dark:bg-gray-900/60">
        @if(!empty($coach->description))
          <div class="prose max-w-none prose-p:leading-relaxed prose-headings:scroll-mt-24
                      dark:prose-invert">
            {!! nl2br(e($coach->description)) !!}
          </div>
        @else
          <p class="text-gray-600 dark:text-gray-300">Coach profile.</p>
        @endif
      </div>

      {{-- Acquisto / prezzi (sticky su desktop) --}}
      <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-5">
        <div class="lg:col-span-3"></div> {{-- spacer per allineamento contenuti --}}

        <div class="lg:col-span-2">
          <div class="lg:sticky lg:top-24">
            <div class="rounded-2xl border border-[color:var(--accent)]/30 bg-white/80 p-5 shadow-sm backdrop-blur
                        dark:border-[color:var(--accent)]/30 dark:bg-gray-900/60">
              @auth
                @if($coach->prices->count())
                  <form method="POST" action="{{ route('cart.add.coach', $coach) }}" class="space-y-3">
                    @csrf
                    <div>
                      <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-200">Select duration</label>
                      <select name="price_id"
                              class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 outline-none transition
                                     focus:ring-2 focus:ring-[var(--accent)]/30 dark:border-gray-800 dark:bg-gray-950 dark:text-gray-100">
                        @foreach($coach->prices as $price)
                          <option value="{{ $price->id }}">
                            {{ $price->duration }} — @money($price->price_cents, $price->currency)
                          </option>
                        @endforeach
                      </select>
                    </div>

                    <button
                      class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-[var(--accent)] px-4 py-2.5 text-white shadow
                             transition hover:opacity-90 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white/60">
                      <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M3 3h2l.4 2M7 13h10l3-6H6.4M7 13l-2 7h14M10 21a1 1 0 1 0 0-2 1 1 0 0 0 0 2Zm8 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z"/></svg>
                      Add to cart
                    </button>
                  </form>
                @else
                  <div class="text-sm text-gray-600 dark:text-gray-300">No Prices available for this coach.</div>
                @endif
              @else
                <a href="{{ route('login') }}"
                   class="inline-flex w-full items-center justify-center gap-2 rounded-xl border px-4 py-2.5 text-sm
                          hover:bg-gray-50 dark:border-gray-800 dark:hover:bg-gray-800">
                  Login to buy coaching
                </a>
              @endauth
            </div>
          </div>
        </div>
      </div>

    {{-- VIDEO --}}
@php
  $publicVideo  = \App\Support\VideoEmbed::from($pack->video_url ?? null);
  $privateVideo = null;

  $canSeePrivate = auth()->check() && \App\Support\Purchases::userHasPack(auth()->id(), $pack->id);

  if ($canSeePrivate && !empty($pack->private_video_url)) {
      $privateVideo = \App\Support\VideoEmbed::from($pack->private_video_url);
  }
  $embedUrl = $privateVideo ?: $publicVideo;
@endphp

@if(!empty($embedUrl))
  <div class="mx-auto max-w-6xl px-4 pt-6">
    <div class="overflow-hidden rounded-2xl ring-1 ring-black/5 dark:ring-white/10">
      <iframe src="{{ $embedUrl }}" class="h-[360px] w-full sm:h-[420px]" frameborder="0" allowfullscreen loading="lazy"></iframe>
    </div>
  </div>
@endif

@php
  $public  = $pack->tutorials()->where('is_public', true)->orderBy('sort_order')->get();
  $private = $canSeePrivate
      ? $pack->tutorials()->where('is_public', false)->orderBy('sort_order')->get()
      : collect();
@endphp

@if($public->count() || $private->count() || $pack->tutorials()->where('is_public', false)->exists())
  <div class="mx-auto max-w-6xl px-4 pb-14">
    <div class="mt-2 rounded-2xl border border-gray-100 p-6 shadow-sm dark:border-gray-800">
      <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-gray-100">Tutorials</h3>

      @if($public->count())
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
          @foreach($public as $t)
            <x-tutorial-card :tutorial="$t" />
          @endforeach
        </div>
      @endif

      @if($private->count())
        <div class="mt-8 border-t pt-4 dark:border-gray-800">
          <div class="mb-3 text-sm text-gray-500">Exclusive for buyers</div>
          <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach($private as $t)
              <x-tutorial-card :tutorial="$t" />
            @endforeach
          </div>
        </div>
      @elseif($pack->tutorials()->where('is_public', false)->exists())
        <div class="mt-8 rounded-lg bg-amber-50 p-3 text-sm text-amber-800 dark:bg-amber-900/20 dark:text-amber-200">
          Some tutorials are available after purchase.
        </div>
      @endif
    </div>
  </div>
@endif
</x-app-layout>
