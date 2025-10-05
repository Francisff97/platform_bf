{{-- resources/views/public/packs/show.blade.php --}}
<x-app-layout>

<x-slot name="header">
  @php
    $titleColorMap = [
      'indigo'  => 'text-indigo-600 dark:text-indigo-300',
      'emerald' => 'text-emerald-600 dark:text-emerald-300',
      'amber'   => 'text-amber-600 dark:text-amber-300',
      'rose'    => 'text-rose-600 dark:text-rose-300',
      'sky'     => 'text-sky-600 dark:text-sky-300',
    ];
    $titleColor = $titleColorMap[$pack->category->color ?? 'indigo'] ?? 'text-gray-900 dark:text-gray-100';

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
    $cat = $pack->category->color ?? 'indigo';
    $badgeCls = ($badgeLight[$cat] ?? 'bg-gray-100 text-gray-700').' '.($badgeDark[$cat] ?? 'dark:bg-gray-800 dark:text-gray-100');
  @endphp

  <div>
    @if($pack->category)
      <span class="mb-2 inline-block rounded-full px-2.5 py-0.5 text-xs font-medium {{ $badgeCls }}">
        {{ $pack->category->name }}
      </span>
    @endif

    <h1 class="text-3xl font-extrabold leading-tight {{ $titleColor }}">
      {{ $pack->title }}
    </h1>
  </div>
</x-slot>

@if($pack->image_path)
  <img src="{{ asset('storage/'.$pack->image_path) }}" class="mb-6 rounded-xl max-h-80 object-cover w-full">
@endif

@php
  $map = [
    'indigo'=>'bg-indigo-50 text-indigo-700',
    'emerald'=>'bg-emerald-50 text-emerald-700',
    'rose'=>'bg-rose-50 text-rose-700',
    'amber'=>'bg-amber-50 text-amber-700',
    'sky'=>'bg-sky-50 text-sky-700',
  ];
  $cls = $map[$pack->category->color ?? 'indigo'] ?? 'bg-gray-100 text-gray-700';
@endphp

@if($pack->category)
  <span class="rounded-full px-2.5 py-0.5 text-xs font-medium {{ $cls }}">{{ $pack->category->name }}</span>
@endif

<p class="text-gray-600 mb-4 dark:text-white">{{ $pack->excerpt }}</p>

<div class="prose max-w-none dark:text-white">{!! nl2br(e($pack->description)) !!}</div>

<div class="mt-4 font-semibold dark:text-white">
  Price: @money($pack->price_cents, $pack->currency)
</div>

<form method="POST" action="{{ route('cart.add.pack',$pack) }}" class="mt-6">
  @csrf
  <button class="rounded bg-[var(--accent)] px-4 py-2 text-white hover:opacity-90">
    Add to cart
  </button>
</form>

@php
  $public = $pack->tutorials()->where('is_public', true)->get();
  $private = collect();
  $canSeePrivate = auth()->check() && auth()->user()->hasPurchasedPack($pack->id);
  if ($canSeePrivate) { 
    $private = $pack->tutorials()->where('is_public', false)->get(); 
  }
@endphp

 @if($public->count() || $private->count())
  <div class="mt-10 rounded-2xl border p-5 dark:border-gray-800">
    <h3 class="mb-4 font-semibold text-lg">Tutorials</h3>

    {{-- PUBLIC --}}
    @if($public->count())
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($public as $t)
          <x-tutorial-card :tutorial="$t" />
        @endforeach
      </div>
    @endif

    {{-- PRIVATE (solo per chi ha acquistato) --}}
    @if($private->count())
      <div class="mt-8 border-t pt-4 dark:border-gray-800">
        <div class="mb-3 text-sm text-gray-500">Exclusive for buyers</div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
          @foreach($private as $t)
            <x-tutorial-card :tutorial="$t" />
          @endforeach
        </div>
      </div>
    @elseif($pack->tutorials()->where('is_public', false)->exists())
      <div class="mt-8 rounded bg-amber-50 p-3 text-sm text-amber-800">
        Some tutorials are available after purchase.
      </div>
    @endif
  </div>
@endif

</x-app-layout>