<x-admin-layout title="Edit Coach">
  <x-slot name="header"><h1 class="text-xl font-bold">Edit Coach</h1></x-slot>

  @php
    $siteCurrency = \App\Support\Currency::site()['code'];
    $rows = collect(old('prices', $coach->prices ?? []))
      ->map(fn($p) => is_object($p) ? ['duration'=>$p->duration,'price_cents'=>$p->price_cents,'currency'=>$p->currency] : $p)
      ->values();
  @endphp

  <form class="mx-auto grid w-full max-w-2xl gap-5 rounded-2xl border border-[color:var(--accent)]/30 bg-white/70 p-6 shadow-sm backdrop-blur
               dark:border-[color:var(--accent)]/30 dark:bg-gray-900/70"
        method="POST" action="{{ route('admin.coaches.update',$coach) }}" enctype="multipart/form-data">
    @csrf @method('PUT')

    {{-- Image + preview --}}
    <div x-data="{preview:null}">
      <label class="mb-1 block text-sm font-medium text-gray-800 dark:text-gray-200">Image</label>
      <input type="file" name="image" accept="image/*"
             @change="preview = $event.target.files?.[0] ? URL.createObjectURL($event.target.files[0]) : null"
             class="w-full rounded-xl border border-[color:var(--accent)] bg-white/90 p-2 text-sm text-black outline-none transition
                    file:mr-3 file:rounded-lg file:border-0 file:bg-[color:var(--accent)] file:px-3 file:py-2 file:text-white
                    hover:border-[color:var(--accent)]/80 focus:ring-2 focus:ring-[color:var(--accent)]
                    dark:bg-black/80 dark:text-white"/>
      @if($coach->image_path)
        <img src="{{ Storage::url($coach->image_path) }}" class="mt-3 h-36 w-full rounded-lg object-cover ring-1 ring-black/5 dark:ring-white/10">
      @endif
      <template x-if="preview"><img :src="preview" class="mt-3 h-36 w-full rounded-lg object-cover ring-1 ring-black/5 dark:ring-white/10" alt="Preview"></template>
    </div>

    <x-input name="name"  label="Name" required :value="old('name',$coach->name)" />
    <x-input name="slug"  label="Slug"          :value="old('slug',$coach->slug)" />
    <x-input name="team"  label="Team"          :value="old('team',$coach->team)" />
    <x-input name="skills" label="Skills" placeholder="Comma separated"
             :value="old('skills', $coach->skills ? implode(', ',$coach->skills) : '')" />

    {{-- Prices repeater (no globals) --}}
    <div 
  x-data="{
    rows: {{ Js::from(old('prices', $coach->prices ?? [['duration'=>'','price_cents'=>'','currency'=>\App\Support\Currency::site()['code']]])) }},
    add() { this.rows.push({duration:'', price_cents:'', currency:'{{ \App\Support\Currency::site()['code'] }}'}); },
    clear() { this.rows = [{duration:'', price_cents:'', currency:'{{ \App\Support\Currency::site()['code'] }}'}]; }
  }"
  class="mt-2"
>
  <div class="mb-1 text-sm font-medium text-gray-800 dark:text-gray-200">Prices</div>

  <template x-for="(row, i) in rows" :key="i">
    <div class="mb-2 grid grid-cols-12 gap-2">
      <input class="col-span-6 h-11 rounded-xl border border-[color:var(--accent)] bg-white/90 px-3 text-sm dark:bg-black/80 dark:text-white"
             type="text" :name="`prices[${i}][duration]`" x-model="row.duration" placeholder="Duration (ex: 30 mins)">
      <input class="col-span-4 h-11 rounded-xl border border-[color:var(--accent)] bg-white/90 px-3 text-sm dark:bg-black/80 dark:text-white"
             type="number" :name="`prices[${i}][price_cents]`" x-model.number="row.price_cents" placeholder="Price in cents" min="0" step="1">
      <input class="col-span-2 h-11 rounded-xl border border-[color:var(--accent)] bg-white/90 px-3 text-sm uppercase dark:bg-black/80 dark:text-white"
             type="text" :name="`prices[${i}][currency]`" x-model="row.currency" readonly>
    </div>
  </template>

  <div class="flex gap-2">
    <button type="button" @click="add()" class="rounded-xl bg-[var(--accent)] px-3 py-2 text-sm text-white hover:opacity-90">+ Add price</button>
    <button type="button" @click="clear()" class="rounded-xl border px-3 py-2 text-sm hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800">Clear</button>
  </div>
</div>

    <div class="mt-1 flex items-center gap-3">
      <button class="rounded-xl bg-[var(--accent)] px-5 py-2.5 text-white hover:opacity-90">Update</button>
      <a href="{{ route('admin.coaches.index') }}" class="text-gray-600 hover:underline dark:text-gray-300">Cancel</a>
    </div>
  </form>
</x-admin-layout>