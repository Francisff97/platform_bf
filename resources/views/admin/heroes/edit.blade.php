<x-admin-layout title="Edit Hero">
  <x-slot name="header"><h1 class="text-xl font-bold">Edit Hero</h1></x-slot>

  @if ($errors->any())
    <div class="mb-4 rounded border border-red-300 bg-red-50 p-3 text-sm text-red-700">
      <div class="mb-2 font-semibold">Please fix these errors:</div>
      <ul class="list-disc pl-5">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
  @endif

  <form method="POST" action="{{ route('admin.heroes.update',$hero) }}" enctype="multipart/form-data"
        class="mx-auto grid max-w-2xl gap-5 rounded-2xl border border-[color:var(--accent)]/30 bg-white/70 p-6 shadow-sm backdrop-blur
               dark:border-[color:var(--accent)]/30 dark:bg-gray-900/70">
    @csrf @method('PUT')

    <div class="rounded-lg bg-gray-50 p-2 text-sm dark:bg-gray-900">
      slug / page: <strong>{{ $hero->page }}</strong>
    </div>

    <div class="grid gap-4 sm:grid-cols-2">
      <div>
        <label class="mb-1 block text-sm font-medium dark:text-gray-200">Title</label>
        <input name="title" value="{{ old('title',$hero->title) }}"
               class="h-11 w-full rounded-xl border border-gray-300 bg-white px-3 text-black outline-none
                      dark:border-gray-700 dark:bg-black/70 dark:text-white">
      </div>
      <div>
        <label class="mb-1 block text-sm font-medium dark:text-gray-200">Subtitle</label>
        <input name="subtitle" value="{{ old('subtitle',$hero->subtitle) }}"
               class="h-11 w-full rounded-xl border border-gray-300 bg-white px-3 text-black outline-none
                      dark:border-gray-700 dark:bg-black/70 dark:text-white">
      </div>
    </div>

    <div class="grid gap-4 sm:grid-cols-2">
      <div>
        <label class="mb-1 block text-sm font-medium dark:text-gray-200">Height (px/vh)</label>
        <input name="height_css" value="{{ old('height_css', $hero->height_css ?? '70vh') }}"
               class="h-11 w-full rounded-xl border border-gray-300 bg-white px-3 text-black outline-none
                      dark:border-gray-700 dark:bg-black/70 dark:text-white">
        <p class="mt-1 text-xs text-gray-500">e.g. <code>70vh</code> or <code>480px</code></p>
      </div>
      <label class="flex items-end gap-2">
        <input type="checkbox" name="full_bleed" value="1" {{ old('full_bleed',$hero->full_bleed ?? true) ? 'checked' : '' }}>
        <span class="text-sm dark:text-gray-200">Full width (edge-to-edge)</span>
      </label>
    </div>

    <div x-data="heroPreviewEdit(@js($hero->image_path ? Storage::url($hero->image_path) : asset('images/placeholder-16x9.jpg')), @js(old('overlay',$hero->overlay)))">
      <label class="mb-1 block text-sm font-medium dark:text-gray-200">Background image</label>
      <input type="file" name="image" accept="image/*" @change="onFile($event)"
             class="w-full rounded-xl border border-gray-300 bg-white p-2 text-sm
                    file:mr-3 file:rounded-lg file:border-0 file:bg-[color:var(--accent)] file:px-3 file:py-2 file:text-white
                    dark:border-gray-700 dark:bg-black/70 dark:text-white"/>

      {{-- Overlay picker --}}
      <div class="mt-5">
        <label class="mb-1 block text-sm font-medium dark:text-gray-200">Overlay</label>
        <div class="flex flex-wrap gap-2">
          <template x-for="p in presets" :key="p.value">
            <button type="button" @click="select(p.value)"
                    class="rounded-full border px-3 py-1.5 text-xs font-semibold hover:opacity-90"
                    :class="value===p.value ? 'border-[color:var(--accent)] text-[color:var(--accent)]' : 'border-gray-300 dark:border-gray-700 dark:text-gray-200'">
              <span x-text="p.label"></span>
            </button>
          </template>
        </div>
        <input type="text" name="overlay" x-model="value"
               class="mt-3 h-11 w-full rounded-xl border border-gray-300 bg-white px-3 text-sm text-black outline-none
                      dark:border-gray-700 dark:bg-black/70 dark:text-white">
      </div>

      {{-- Preview --}}
      <div class="mt-5 overflow-hidden rounded-2xl ring-1 ring-black/5 dark:ring-white/10">
        <div class="relative aspect-[16/9] w-full">
          <img :src="preview" class="absolute inset-0 h-full w-full object-cover" alt="">
          <div class="absolute inset-0" :class="overlayClass"></div>
          <div class="absolute inset-x-0 bottom-0 p-4">
            <div class="rounded-md bg-black/35 px-2 py-1 text-xs text-white backdrop-blur">Live overlay preview</div>
          </div>
        </div>
      </div>
    </div>

    <label class="inline-flex items-center gap-2">
      <input type="checkbox" name="is_active" value="1" {{ old('is_active',$hero->is_active)?'checked':'' }}>
      <span class="text-sm dark:text-gray-200">Active</span>
    </label>

    <div class="pt-2">
      <button class="inline-flex items-center justify-center rounded-xl bg-[color:var(--accent)] px-5 py-2.5 text-white transition hover:opacity-90">
        Update
      </button>
      <a href="{{ route('admin.heroes.index') }}" class="ml-3 text-sm underline">Cancel</a>
    </div>
  </form>

  <script>
    function heroPreviewEdit(initialUrl, initialOverlay){
      return {
        preview: initialUrl,
        value: initialOverlay || 'from-black/50 via-black/20 to-black/60',
        get overlayClass(){ return `bg-gradient-to-b ${this.value}` },
        presets: [
          { label:'Soft',    value:'from-black/30 via-black/10 to-black/40' },
          { label:'Bold',    value:'from-black/70 via-black/40 to-black/80' },
          { label:'Top → 0', value:'from-black/60 via-transparent to-transparent' },
          { label:'Bottom',  value:'from-transparent via-transparent to-black/70' },
          { label:'None',    value:'from-transparent via-transparent to-transparent' },
        ],
        select(v){ this.value = v; },
        onFile(e){ const f=e.target.files?.[0]; this.preview = f ? URL.createObjectURL(f) : this.preview; }
      }
    }
  </script>
</x-admin-layout>