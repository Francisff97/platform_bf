<x-admin-layout title="Add Coach">
  <form class="max-w-xl grid gap-4" method="POST" action="{{ route('admin.coaches.store') }}" enctype="multipart/form-data">
    @csrf
    <input name="name" class="border p-2 rounded" placeholder="Name" value="{{ old('name') }}" required>
    <input name="slug" class="border p-2 rounded" placeholder="Slug (optional)" value="{{ old('slug') }}">
    <input name="team" class="border p-2 rounded" placeholder="Team" value="{{ old('team') }}">
    <input type="file" name="image" accept="image/*" class="border p-2 rounded">
    <input name="skills" class="border p-2 rounded" placeholder="Skills (separate with)" value="{{ old('skills') }}">

    <h3 class="text-lg font-semibold mt-6">Prices</h3>
    <div id="prices-wrapper">
      @php
        $siteCurrency = \App\Support\Currency::site()['code'];
      @endphp

      @foreach(old('prices', $coach->prices ?? []) as $i => $p)
        <div class="flex gap-2 mb-2">
          <input type="text" name="prices[{{ $i }}][duration]" 
                 value="{{ $p['duration'] ?? $p->duration ?? '' }}" 
                 placeholder="Duration (ex: 30 mins)" 
                 class="border p-2 rounded w-1/2">

          <input type="number" name="prices[{{ $i }}][price_cents]" 
                 value="{{ $p['price_cents'] ?? $p->price_cents ?? '' }}" 
                 placeholder="Price in cents (ex: 50 USD is 5000)" 
                 class="border p-2 rounded w-1/3">

          <input type="text" name="prices[{{ $i }}][currency]" 
                 value="{{ $p['currency'] ?? $p->currency ?? $siteCurrency }}" 
                 class="border p-2 rounded w-1/6">
        </div>
      @endforeach

      <button type="button" onclick="addPriceRow()" class="mt-2 rounded bg-indigo-600 px-3 py-1.5 text-white">
        + Add price
      </button>
    </div>

    <script>
      const platformCurrency = @json(\App\Support\Currency::site()['code']); // valuta di default
      let priceIndex = {{ isset($coach) ? $coach->prices->count() : 0 }};
      function addPriceRow(){
        const wrapper=document.getElementById('prices-wrapper');
        wrapper.insertAdjacentHTML('beforeend',`
          <div class="flex gap-2 mb-2">
            <input type="text" name="prices[${priceIndex}][duration]" placeholder="Duration (ex: 30 mins)" class="border p-2 rounded w-1/2">
            <input type="number" name="prices[${priceIndex}][price_cents]" placeholder="Price in cents (ex: 50 USD is 5000)" class="border p-2 rounded w-1/3">
            <input type="text" name="prices[${priceIndex}][currency]" value="${platformCurrency}" class="border p-2 rounded w-1/6">
          </div>
        `);
        priceIndex++;
      }
    </script>

    <div>
      <button class="rounded bg-[var(--accent)] px-4 py-2 text-white">Save</button>
      <a href="{{ route('admin.coaches.index') }}" class="ml-3 underline">Cancel</a>
    </div>

    @error('name')<p class="text-red-600 text-sm">{{ $message }}</p>@enderror
    @error('image')<p class="text-red-600 text-sm">{{ $message }}</p>@enderror
  </form>
</x-admin-layout>