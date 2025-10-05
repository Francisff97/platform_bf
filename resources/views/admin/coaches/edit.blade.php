<x-admin-layout title="Edit Coach">
  <form class="max-w-xl grid gap-4" method="POST" action="{{ route('admin.coaches.update',$coach) }}" enctype="multipart/form-data">
    @csrf @method('PUT')
    <input placeholder="name" name="name" class="border p-2 rounded" value="{{ old('name',$coach->name) }}" required>
    <input placeholder="slug" name="slug" class="border p-2 rounded" value="{{ old('slug',$coach->slug) }}">
    <input placeholder="team" name="team" class="border p-2 rounded" value="{{ old('team',$coach->team) }}">
    <input type="file" name="image" accept="image/*" class="border p-2 rounded">
    @if($coach->image_path)
      <img src="{{ Storage::url($coach->image_path) }}" class="h-16 rounded">
    @endif
    <input placeholder="skills" name="skills" class="border p-2 rounded" value="{{ old('skills', $coach->skills ? implode(', ',$coach->skills) : '') }}">

    <h3 class="text-lg font-semibold mt-6">Prices</h3>
    <div id="prices-wrapper">
      @php
        $siteCurrency = \App\Support\Currency::site()['code'];
      @endphp

      @foreach(old('prices', $coach->prices ?? []) as $i => $p)
        <div class="flex gap-2 mb-2">
          <input
            type="text"
            name="prices[{{ $i }}][duration]"
            value="{{ $p['duration'] ?? $p->duration ?? '' }}"
            placeholder="Duration (ex: 30 mins)"
            class="border p-2 rounded w-1/2">

          <input
            type="number"
            name="prices[{{ $i }}][price_cents]"
            value="{{ $p['price_cents'] ?? $p->price_cents ?? '' }}"
            placeholder="Price in cents"
            class="border p-2 rounded w-1/3">

          <input
            type="text"
            name="prices[{{ $i }}][currency]"
            value="{{ $p['currency'] ?? $p->currency ?? $siteCurrency }}"
            class="border p-2 rounded w-1/6"
            readonly>
        </div>
      @endforeach

      <button type="button" onclick="addPriceRow()" class="mt-2 rounded bg-indigo-600 px-3 py-1.5 text-white mb-[20px]">
        + Add price
      </button>
    </div>

    <script>
      const platformCurrency = @json(\App\Support\Currency::site()['code']); // es. "EUR" / "USD"
      let priceIndex = (() => {
        // se è una Collection usa count(), se è array usa length, altrimenti 0
        @php
          $countExisting = is_countable($coach->prices ?? null) ? count($coach->prices) : 0;
        @endphp
        return {{ $countExisting }};
      })();

      function addPriceRow(){
        const wrapper = document.getElementById('prices-wrapper');
        wrapper.insertAdjacentHTML('beforeend', `
          <div class="flex gap-2 mb-2">
            <input
              type="text"
              name="prices[\${priceIndex}][duration]"
              placeholder="Duration (ex: 30 mins)"
              class="border p-2 rounded w-1/2">

            <input
              type="number"
              name="prices[\${priceIndex}][price_cents]"
              placeholder="Price in cents"
              class="border p-2 rounded w-1/3">

            <input
              type="text"
              name="prices[\${priceIndex}][currency]"
              value="\${platformCurrency}"
              class="border p-2 rounded w-1/6"
              readonly>
          </div>
        `);
        priceIndex++;
      }
    </script>

    <div>
      <button class="rounded bg-[var(--accent)] px-4 py-2 text-white">Update</button>
      <a href="{{ route('admin.coaches.index') }}" class="ml-3 underline">Cancel</a>
    </div>
  </form>
</x-admin-layout>