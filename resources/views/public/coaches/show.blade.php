<x-app-layout>
  <x-slot name="header">
    <h1 class="text-2xl font-bold">{{ $coach->name }}</h1>
  </x-slot>

  <div class="grid gap-8 md:grid-cols-3">
    {{-- Colonna sinistra: immagine + info --}}
    <div class="md:col-span-1">
      <div class="overflow-hidden rounded-xl">
        @if($coach->image_path)
          <x-img :src="Storage::url($coach->image_path)" class="w-full object-cover" />
        @else
          <div class="h-48 rounded-xl bg-gray-200 dark:bg-gray-800"></div>
        @endif
      </div>

      <div class="mt-4 text-sm text-gray-600 dark:text-gray-300">
        <div><span class="font-medium">Team:</span> {{ $coach->team ?? '—' }}</div>

        @if(!empty($coach->skills) && is_iterable($coach->skills))
          <div class="mt-2">
            <span class="font-medium">Skills:</span>
            @foreach($coach->skills as $s)
              <span class="ml-2 rounded-full bg-gray-100 px-2 py-0.5 text-xs dark:text-gray-800">{{ $s }}</span>
            @endforeach
          </div>
        @endif
      </div>
    </div>

    {{-- Colonna destra: descrizione + acquisto --}}
    <div class="md:col-span-2">
      @if(!empty($coach->description))
        <div class="prose max-w-none dark:prose-invert">
          {!! nl2br(e($coach->description)) !!}
        </div>
      @else
        <p class="text-gray-600 dark:text-gray-300">Coach profile.</p>
      @endif

      @auth
        @if($coach->prices->count())
          <form method="POST" action="{{ route('cart.add.coach', $coach) }}" class="mt-6 max-w-sm">
            @csrf
            <label class="mb-1 block text-sm font-medium">Select duration</label>
            <select name="price_id" class="w-full rounded border p-2 dark:text-gray-800">
              @foreach($coach->prices as $price)
                <option value="{{ $price->id }}">
                  {{ $price->duration }} — @money($price->price_cents, $price->currency)
                </option>
              @endforeach
            </select>

            <button class="mt-3 rounded bg-[var(--accent)] px-4 py-2 text-white hover:opacity-90">
              Add to cart
            </button>
          </form>
        @else
          <p class="mt-6 text-sm text-gray-500">No Prices available for this coach.</p>
        @endif
      @else
        <a href="{{ route('login') }}" class="mt-6 inline-block rounded border px-4 py-2 hover:bg-gray-50 dark:hover:bg-gray-800">
          Login to buy coaching
        </a>
      @endauth

      {{-- Tutorials (come per i pack) --}}
      @php
        $public  = $coach->tutorials()->where('is_public', true)->get();
        $private = collect();

        $canSeePrivate = auth()->check()
          && method_exists(auth()->user(), 'hasPurchasedCoach')
          && auth()->user()->hasPurchasedCoach($coach->id);

        if ($canSeePrivate) {
          $private = $coach->tutorials()->where('is_public', false)->get();
        }
      @endphp

      @if($public->count() || $private->count())
        <div class="mt-10 rounded-2xl border p-5 dark:border-gray-800">
          <h3 class="mb-4 text-lg font-semibold">Tutorials</h3>

          {{-- PUBLIC --}}
          @if($public->count())
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
              @foreach($public as $t)
                <x-tutorial-card :tutorial="$t" />
              @endforeach
            </div>
          @endif

          {{-- PRIVATE --}}
          @if($private->count())
            <div class="mt-8 border-t pt-4 dark:border-gray-800">
              <div class="mb-3 text-sm text-gray-500">Exclusive for buyers</div>
              <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($private as $t)
                  <x-tutorial-card :tutorial="$t" />
                @endforeach
              </div>
            </div>
          @elseif($coach->tutorials()->where('is_public', false)->exists())
            <div class="mt-8 rounded bg-amber-50 p-3 text-sm text-amber-800">
              Some tutorials are available after purchase.
            </div>
          @endif
        </div>
      @endif
    </div>
  </div>
</x-app-layout>
