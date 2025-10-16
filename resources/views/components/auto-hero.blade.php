@php
  use Illuminate\Support\Facades\Route;

  $routeName = Route::currentRouteName();
  $pageKey = match (true) {
    Route::is('home')          => 'home',
    Route::is('packs.public')  => 'packs',
    Route::is('packs.show')    => 'packs.show',
    Route::is('services.public') => 'services',
    Route::is('builders.index') => 'builders',
    Route::is('builders.show')  => 'builders.show',
    Route::is('coaches.index')  => 'coaches',
    Route::is('coaches.show')   => 'coaches.show',
    Route::is('about')          => 'about',
    Route::is('contacts')       => 'contacts',
    default => null,
  };

  $hero = \App\Models\Hero::where('is_active', true)
            ->when($pageKey, fn($q) => $q->where('page', $pageKey))
            ->orderBy('sort_order')
            ->first()
          ?: \App\Models\Hero::where('is_active', true)->whereNull('page')->orderBy('sort_order')->first();
@endphp

@if($hero)
  <x-hero :hero="$hero" />
@endif