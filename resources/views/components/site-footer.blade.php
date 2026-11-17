@php
  $s = \App\Models\SiteSetting::first();
  $logoLight = $s?->logo_light_path ? Storage::url($s->logo_light_path) : null;
  $logoDark  = $s?->logo_dark_path  ? Storage::url($s->logo_dark_path)  : null;
  $discord   = $s?->discord_url ?? $s?->discord_link ?? '#';

  $links = [
    ['label'=>'Home','route'=>'home'],
    ['label'=>'About','route'=>'about'],
    ['label'=>'Services','route'=>'services.public'],
    ['label'=>'Builders','route'=>'builders.index'],
    ['label'=>'Coaches','route'=>'coaches.index'],
    ['label'=>'Packs','route'=>'packs.public'],
    ['label'=>'Contacts','route'=>'contacts'],
  ];
@endphp

<footer class="mt-16 border-t bg-white dark:bg-gray-900 dark:border-gray-800">
  <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-10 grid gap-10 md:grid-cols-3">
    {{-- Colonna 1: logo + discord --}}
    <div>
      <a href="{{ route('home') }}" class="inline-flex items-center gap-2">
        @if($logoLight || $logoDark)
          <img src="{{ $logoLight }}" class="h-10 dark:hidden" alt="Logo">
          <img src="{{ $logoDark  }}" class="h-10 hidden dark:block" alt="Logo dark">
        @else
          <span class="text-lg font-semibold">{{ $s->brand_name ?? config('app.name') }}</span>
        @endif
      </a>
      <div class="mt-4">
        <a href="{{ $discord }}" class="inline-flex items-center gap-2 rounded-full px-3 py-1.5 text-white hover:opacity-90" style="background: var(--accent);">
          Join our Discord
        </a>
      </div>
    </div>

    {{-- Colonna 2: menu --}}
    <nav class="grid grid-cols-2 gap-2 md:justify-items-center">
      @foreach($links as $l)
        @if(Route::has($l['route']))
          <a href="{{ route($l['route']) }}" class="text-sm text-gray-700 hover:text-[var(--accent)] dark:text-gray-300">{{ $l['label'] }}</a>
        @endif
      @endforeach
    </nav>

    {{-- Colonna 3: policy + contatti --}}
    <div class="md:text-right space-y-2">
      @if(Route::has('privacy')) <a href="{{ route('privacy') }}" class="text-sm hover:underline">Privacy Policy</a><br>@endif
      @if(Route::has('cookies')) <a href="{{ route('cookies') }}" class="text-sm hover:underline">Cookie Policy</a><br>@endif
      <a href="{{ route('contacts') }}" class="text-sm hover:underline">Contact us</a>
    </div>
  </div>

  <div class="border-t dark:border-gray-800 py-4 text-center text-xs text-gray-500">
    Created with ❤️ from <a href="https://www.francescodesign.me" target="_blank">Francesco Design</a>
  </div>
</footer>