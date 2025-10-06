{{-- resources/views/home.blade.php --}}
<x-app-layout>
{{-- SLIDER FULL WIDTH (full-bleed) --}}
<style>
  .full-bleed{width:100vw;position:relative;left:50%;right:50%;margin-left:-50vw;margin-right:-50vw}

  /* ✅ Forza Swiper a non imporre altezze */
  #homeHero,
  #homeHero .swiper,
  #homeHero .swiper-wrapper,
  #homeHero .swiper-slide { height: auto !important; }

  /* ✅ Card della slide a 70vh (fallback min 480px) */
  #homeHero .slide-figure{
    height: 70vh;
    min-height: 480px;
  }
  /* iOS safer units (svh) dove supportati */
  @supports (height: 70svh){
    #homeHero .slide-figure{ height: 70svh; }
  }

  /* ✅ Niente max-height sulle immagini */
  #homeHero img{ max-height: none !important; }

  /* ✅ Controlli bianchi */
  #homeHero .swiper-button-next, 
  #homeHero .swiper-button-prev { color:#fff; }
  #homeHero .swiper-pagination-bullet{ background:rgba(255,255,255,.6); opacity:1; }
  #homeHero .swiper-pagination-bullet-active{ background:#fff; }
  @media screen and (max-width: 767px){
    #homeHero .slide-figure{
    height: 50vh;
    min-height: 480px;
    padding: 40px;
  }
  }
</style>

<section class="full-bleed">
  <div id="homeHero" class="swiper w-full">
    <div class="swiper-wrapper">
      @foreach($slides as $s)
        <div class="swiper-slide">
          <figure class="slide-figure relative w-full">
            @if($s->image_path)
              <img src="{{ Storage::url($s->image_path) }}"
                   alt="{{ $s->title }}"
                   class="absolute inset-0 h-full w-full object-cover">
            @else
              <div class="absolute inset-0 bg-gradient-to-r from-gray-200 to-gray-300 dark:from-gray-900 dark:to-gray-800"></div>
            @endif

            {{-- overlay per contrasto testo --}}
            <div class="absolute inset-0 bg-gradient-to-b from-black/55 via-black/35 to-black/60"></div>

            {{-- contenuto centrato, boxato a 1200 --}}
            <figcaption class="relative z-10 mx-auto flex h-full max-w-[1200px] items-center px-4 sm:px-6">
              <div class="max-w-xl">
                @if($s->title)
                  <h2 class="font-orbitron text-3xl sm:text-4xl md:text-5xl text-white
                             drop-shadow-[0_2px_8px_rgba(0,0,0,0.6)]">
                    {{ $s->title }}
                  </h2>
                @endif
                @if($s->subtitle)
                  <p class="mt-2 text-white/90">{{ $s->subtitle }}</p>
                @endif
                @if($s->cta_url)
                  <a href="{{ $s->cta_url }}"
                     class="mt-4 inline-flex items-center rounded-full px-4 py-2 text-white hover:opacity-90"
                     style="background:var(--accent)">
                    {{ $s->cta_label ?? 'Learn more' }}
                  </a>
                @endif
              </div>
            </figcaption>
          </figure>
        </div>
      @endforeach
    </div>

    <div class="swiper-pagination !bottom-3"></div>
    <div class="swiper-button-prev"></div>
    <div class="swiper-button-next"></div>
  </div>
</section>

<script>
  document.addEventListener('DOMContentLoaded', () => {
    if (window.Swiper) {
      new Swiper('#homeHero', {
        loop: true,
        autoplay: { delay: 4500, disableOnInteraction: false },
        pagination: { el: '#homeHero .swiper-pagination', clickable: true },
        navigation: { nextEl: '#homeHero .swiper-button-next', prevEl: '#homeHero .swiper-button-prev' },
      });
    }
  });
   // caroselli orizzontali (free scroll, responsive)
   document.querySelectorAll('.swiper.swiper-auto').forEach((el) => {
    new Swiper(el, {
      slidesPerView: 'auto',
      spaceBetween: 16,
      freeMode: true,
    });
  });
</script>



  {{-- Packs --}}
  <section class="max-w-6xl mx-auto my-[100px]">
    <h2 class="text-2xl font-bold mb-6">Our packs</h2>
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
      <div class="md:col-span-2 row-span-2 h-full">
        {{-- Pack più recente grande --}}
        @if($latestPack)
          <a href="{{ route('packs.show',$latestPack->slug) }}"
             class="block overflow-hidden rounded-xl shadow bg-white dark:bg-gray-800">
            <img src="{{ asset('storage/'.$latestPack->image_path) }}" class="h-[300px]] md:h-[400px] w-full object-cover">
            <div class="p-4">
              <h3 class="font-semibold">{{ $latestPack->title }}</h3>
              <p class="text-sm text-gray-600 dark:text-gray-300">{{ $latestPack->excerpt }}</p>
              <div class="font-bold text-[var(--accent)] mt-2">
                {{ number_format($latestPack->price_cents/100,2,',','.') }} {{ $latestPack->currency }}
              </div>
            </div>
          </a>
        @endif
      </div>
      @foreach($otherPacks as $p)
        <a href="{{ route('packs.show',$p->slug) }}"
           class="block overflow-hidden rounded-xl shadow bg-white dark:bg-gray-800">
          <img src="{{ asset('storage/'.$p->image_path) }}" class="h-[300px] md:h-32 w-full object-cover">
          <div class="p-3">
            <h3 class="font-semibold text-sm">{{ $p->title }}</h3>
            <div class="text-xs text-gray-600 dark:text-gray-300">{{ $p->excerpt }}</div>
            <div class="font-bold text-[var(--accent)] mt-1">
              {{ number_format($p->price_cents/100,2,',','.') }} {{ $p->currency }}
            </div>
          </div>
        </a>
      @endforeach
    </div>
  </section>

  {{-- About us --}}
  @php
  $aboutFeature = \App\Models\AboutSection::featured()->where('layout','image_left')->ordered()->first();
@endphp
@if($aboutFeature)
  @include('partials.about-section', ['s' => $aboutFeature])
@endif

  {{-- BUILDERS carousel --}}
<section class="py-10 sm:py-12">
  <h3 class="font-orbitron text-xl sm:text-2xl mb-4">Our Builders</h3>
  <div class="swiper swiper-auto">
    <div class="swiper-wrapper">
      @foreach($builders as $b)
        <div class="swiper-slide !w-[240px]">
          <a href="{{ route('builders.show',$b->slug) }}"
             class="block rounded-2xl border bg-white p-4 shadow-sm hover:shadow dark:border-gray-800 dark:bg-gray-900">
            <div class="mx-auto h-16 w-16 overflow-hidden rounded-full bg-gray-200 dark:bg-gray-800">
              @if($b->image_path)<img src="{{ Storage::url($b->image_path) }}" class="h-full w-full object-cover">@endif
            </div>
            <div class="mt-2 text-center">
              <div class="font-semibold line-clamp-1">{{ $b->name }}</div>
              <div class="text-xs text-gray-500 line-clamp-1">{{ $b->team ?? '—' }}</div>
            </div>
          </a>
        </div>
      @endforeach
    </div>
  </div>
</section>

{{-- COACHES carousel (mostra solo se esistono) --}}
@if($coaches->isNotEmpty())
<section class="py-10 sm:py-12">
  <h3 class="font-orbitron text-xl sm:text-2xl mb-4">Our Coaches</h3>
  <div class="swiper swiper-auto">
    <div class="swiper-wrapper">
      @foreach($coaches as $c)
        <div class="swiper-slide !w-[220px]">
          <a href="{{ route('coaches.show',$c->slug) }}"
             class="block rounded-2xl border bg-white p-4 text-center shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="mx-auto h-16 w-16 overflow-hidden rounded-full bg-gray-200 dark:bg-gray-800">
              @if($c->image_path)<img src="{{ Storage::url($c->image_path) }}" class="h-full w-full object-cover">@endif
            </div>
            <div class="mt-2 font-semibold line-clamp-1">{{ $c->name }}</div>
            <div class="text-xs text-gray-500 line-clamp-1">{{ $c->team ?? '—' }}</div>
          </a>
        </div>
      @endforeach
    </div>
  </div>
</section>
@endif

{{-- SERVICES carousel --}}
<section class="py-10 sm:py-12">
  <h3 class="font-orbitron text-xl sm:text-2xl mb-4">Our Services</h3>
  <div class="swiper swiper-auto">
    <div class="swiper-wrapper">
      @foreach($services as $s)
        <div class="swiper-slide !w-[280px]">
          <div class="rounded-2xl border bg-white shadow-sm overflow-hidden dark:border-gray-800 dark:bg-gray-900">
            @if($s->image_path)
              <img src="{{ Storage::url($s->image_path) }}" class="w-full aspect-[16/9] object-cover">
            @endif
            <div class="p-4">
              <div class="font-semibold line-clamp-1">{{ $s->title }}</div>
              <div class="mt-1 text-sm text-gray-600 dark:text-gray-300 line-clamp-2">{{ $s->excerpt }}</div>
            </div>
          </div>
        </div>
      @endforeach
    </div>
  </div>
</section>


  {{-- Contact us --}}
  <section class="w-full full-bleed bg-[var(--accent)] text-white mt-[100px] py-12">
    <div class="max-w-6xl mx-auto grid md:grid-cols-2 gap-8 px-4 items-center">
      <div>
        <h2 class="text-xl font-semibold mb-3">Contact us</h2>
        <p>Email: info@example.com</p>
      </div>
      <div class="flex flex-col sm:flex-row gap-4">
      <a class="text-black py-4 w-fit bg-white rounded px-6" href="/contacts">Contact us!</a>
      @php
          $s = \App\Models\SiteSetting::first();
$discord   = $s?->discord_url ?? $s?->discord_link ?? '#';
@endphp
<a href="{{ $discord }}" class="inline-flex items-center gap-2 rounded-full px-3 py-1.5 text-white hover:opacity-90" style="background: #212121;">
          Join our Discord
        </a>
      </div>    
    </div>
  </section>
</x-app-layout>
