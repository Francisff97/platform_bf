{{-- resources/views/public/about.blade.php --}}
<x-app-layout>
 <x-auto-hero />

  @php
    // Evita "Undefined variable" e normalizza a Collection
    $sections = ($sections ?? collect());
    $hasSections = $sections->isNotEmpty();

    $s = \App\Models\SiteSetting::first();
    $logoLight = $s?->logo_light_path ? Storage::url($s->logo_light_path) : null;
    $logoDark  = $s?->logo_dark_path  ? Storage::url($s->logo_dark_path)  : null;
  @endphp

  @if($hasSections)
    <div class="space-y-10 my-[50px]">
      @foreach($sections as $sct)
        @include('partials.about-section', ['s' => $sct])
      @endforeach
    </div>
  @else
    {{-- Fallback: pagina in manutenzione --}}
    <div class="min-h-[60vh] flex items-center justify-center">
      <div class="text-center">
        {{-- Logo (light/dark) se presente, altrimenti il nome brand --}}
        @if($logoLight || $logoDark)
          <img src="{{ $logoLight ?? '' }}" class="mx-auto h-12 w-auto dark:hidden" alt="Logo">
          <img src="{{ $logoDark ?? '' }}"  class="mx-auto hidden h-12 w-auto dark:block" alt="Logo Dark">
        @else
          <div class="mx-auto mb-3 text-xl font-bold"> {{ $s->brand_name ?? config('app.name','Blueprint') }} </div>
        @endif

        <h2 class="mt-4 text-2xl font-semibold text-gray-900 dark:text-gray-100">Page on Mantainance</h2>
        <p class="mt-2 text-gray-600 dark:text-gray-300">
          We are working on this page, come back later!
        </p>
        <a href="{{ route('home') }}"
           class="mt-6 inline-flex items-center rounded-full px-4 py-2 text-white hover:opacity-90"
           style="background: var(--accent);">
          Back to home
        </a>
      </div>
    </div>
  @endif
</x-app-layout>
