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

  {{-- SCELTA VIDEO DA MOSTRARE --}}
@php
  $isBuyer = auth()->check() && (
    method_exists(\App\Support\Purchases::class, 'userHasPack')
    && \App\Support\Purchases::userHasPack(auth()->id(), $pack->id)
  );

  // Tutorial primario: se acquirente prendo anche i privati, altrimenti solo pubblici
  $primaryTutorial = $isBuyer
    ? $pack->tutorials()->orderBy('sort_order')->first()
    : $pack->tutorials()->where('is_public', true)->orderBy('sort_order')->first();

  $embedUrl = null;
  if ($primaryTutorial && $primaryTutorial->video_url) {
    $embedUrl = \App\Support\VideoEmbed::from($primaryTutorial->video_url);
  }
  // fallback sul campo video_url del pack
  if (!$embedUrl && !empty($pack->video_url)) {
    $embedUrl = \App\Support\VideoEmbed::from($pack->video_url);
  }
@endphp

{{-- PLAYER (solo se URL valido) --}}
@if($embedUrl)
  <div class="mx-auto max-w-6xl px-4 pt-6">
    <div class="overflow-hidden rounded-2xl ring-1 ring-black/5 dark:ring-white/10">
      <iframe
        src="{{ $embedUrl }}"
        class="h-[360px] w-full sm:h-[420px]"
        frameborder="0"
        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
        allowfullscreen
        loading="lazy"
      ></iframe>
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
              <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M3 3h2l.4 2M7 13h10l3-6H6.4M7 13l-2 7h14M10 21a1 1 0 1 0 0-2 1 1 0 0 0 0 2Zm8 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z"/></svg>
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

  {{-- ====== VIDEO (pubblico/privato) ====== --}}
@php
  /** Decidi se l’utente è buyer */
  $isBuyer = auth()->check() && method_exists(auth()->user(), 'hasPurchasedPack')
    ? auth()->user()->hasPurchasedPack($pack->id)
    : false;

  // 1) Se esistono tutorial: scegli il "primario"
  //    - buyer: qualsiasi (anche privati), ordinati
  //    - non buyer: solo pubblici, ordinati
  $primaryTutorial = $isBuyer
    ? $pack->tutorials()->orderBy('sort_order')->first()
    : $pack->tutorials()->where('is_public', true)->orderBy('sort_order')->first();

  // 2) Se non trovi tutorial, fallback ai campi sul Pack (se li usi)
  $rawUrl = null;
  if ($primaryTutorial && !empty($primaryTutorial->video_url)) {
    $rawUrl = $primaryTutorial->video_url;
  } else {
    // eventuali campi video sul pack
    $rawUrl = $isBuyer
      ? ($pack->private_video_url ?? $pack->video_url ?? null)
      : ($pack->video_url ?? null);
  }

  $embedUrl = \App\Support\VideoEmbed::from($rawUrl);
@endphp

@if($embedUrl)
  {{-- Wrapper con rapporto 16:9 che evita "altezza 0" --}}
  <div class="mx-auto max-w-6xl px-4 pt-6">
    <div style="position:relative;width:100%;padding-top:56.25%;overflow:hidden;border-radius:16px;box-shadow:0 1px 3px rgba(0,0,0,.08)">
      <iframe
        src="{{ $embedUrl }}"
        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
        allowfullscreen
        loading="lazy"
        style="position:absolute;top:0;left:0;width:100%;height:100%;border:0">
      </iframe>
    </div>

    {{-- debug temporaneo (puoi rimuoverlo) --}}
    {{-- <div class="mt-2 text-xs text-gray-500">EMBED: {{ $embedUrl }}</div> --}}
  </div>
@endif

{{-- ====== TUTORIALS GRID ====== --}}
@php
  $public  = $pack->tutorials()->where('is_public', true)->orderBy('sort_order')->get();
  $private = $isBuyer
    ? $pack->tutorials()->where('is_public', false)->orderBy('sort_order')->get()
    : collect();
@endphp

@if($public->count() || $private->count() || $pack->tutorials()->where('is_public', false)->exists())
  <div class="mx-auto max-w-6xl px-4 pb-14">
    <div class="mt-2 rounded-2xl border border-gray-100 p-6 shadow-sm dark:border-gray-800">
      <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-gray-100">Tutorials</h3>

      {{-- PUBLIC --}}
      @if($public->count())
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
          @foreach($public as $t)
            <x-tutorial-card :tutorial="$t" />
          @endforeach
        </div>
      @endif

      {{-- PRIVATE (solo buyer) --}}
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
          Some tutorials are available after 
        </div>
      @endif
    </div>
  </div>
@endif
                    {{-- debugging --}}
<div class="my-2 text-sm text-red-600">EMBED URL: {{ $embedUrl ?? 'null' }}</div>
                    <iframe src="https://www.youtube.com/embed/dRnR6oBuEYE" width="560" height="315"></iframe>
</x-app-layout>
