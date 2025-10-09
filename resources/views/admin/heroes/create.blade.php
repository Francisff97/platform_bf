<x-admin-layout title="Create Hero Section">
  <x-slot name="header"><h1 class="text-xl font-bold">New Hero</h1></x-slot>

  @if ($errors->any())
    <div class="mb-4 rounded-xl border border-red-300 bg-red-50/80 px-4 py-3 text-sm text-red-700
                dark:border-red-500/40 dark:bg-red-900/20 dark:text-red-200">
      <div class="mb-2 font-semibold">Please fix these errors:</div>
      <ul class="list-disc pl-5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
  @endif

  <form method="POST" action="{{ route('admin.heroes.store') }}" enctype="multipart/form-data"
        class="mx-auto grid w-full max-w-2xl gap-5 rounded-2xl border border-[color:var(--accent)]/30 bg-white/70 p-6 shadow-sm backdrop-blur
               dark:border-[color:var(--accent)]/30 dark:bg-gray-900/70">
    @csrf

    {{-- ⚠️ Duplicato nel sorgente originale: input text "page".
         Lo lascio commentato per evitare conflitti con il select sottostante. --}}
    {{-- 
    <div>
      <label class="mb-1 block text-sm font-medium text-gray-800 dark:text-gray-200">Page (slug)</label>
      <input name="page" class="h-11 w-full rounded-xl border border-[color:var(--accent)] bg-white/90 px-3 dark:bg-black/80 dark:text-white"
             placeholder="es. home / packs / services" value="{{ old('page') }}">
    </div>
    --}}

    {{-- TITLE --}}
    <div>
      <label class="mb-1 block text-sm font-medium text-gray-800 dark:text-gray-200">Title</label>
      <input name="title" value="{{ old('title') }}"
             class="h-11 w-full rounded-xl border border-[color:var(--accent)] bg-white/90 px-3 placeholder:text-gray-500 outline-none transition
                    hover:border-[color:var(--accent)]/80 focus:ring-2 focus:ring-[color:var(--accent)]
                    dark:bg-black/80 dark:text-white dark:placeholder:text-gray-400"/>
    </div>

    {{-- SUBTITLE --}}
    <div>
      <label class="mb-1 block text-sm font-medium text-gray-800 dark:text-gray-200">Subtitle</label>
      <input name="subtitle" value="{{ old('subtitle') }}"
             class="h-11 w-full rounded-xl border border-[color:var(--accent)] bg-white/90 px-3 placeholder:text-gray-500 outline-none transition
                    hover:border-[color:var(--accent)]/80 focus:ring-2 focus:ring-[color:var(--accent)]
                    dark:bg-black/80 dark:text-white dark:placeholder:text-gray-400"/>
    </div>

    {{-- IMAGE + live preview --}}
    <div x-data="{preview:null}">
      <label class="mb-1 block text-sm font-medium text-gray-800 dark:text-gray-200">Image</label>
      <input type="file" name="image" accept="image/*"
             @change="preview = $event.target.files?.[0] ? URL.createObjectURL($event.target.files[0]) : null"
             class="w-full rounded-xl border border-[color:var(--accent)] bg-white/90 p-2 text-sm outline-none transition
                    file:mr-3 file:rounded-lg file:border-0 file:bg-[color:var(--accent)] file:px-3 file:py-2 file:text-white
                    hover:border-[color:var(--accent)]/80 focus:ring-2 focus:ring-[color:var(--accent)]
                    dark:bg-black/80 dark:text-white"/>
      <template x-if="preview">
        <img :src="preview" class="mt-3 h-36 w-full rounded-lg object-cover ring-1 ring-black/5 dark:ring-white/10" alt="Preview">
      </template>
      @error('image') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>

    {{-- HEIGHT + FULL BLEED --}}
    <div class="grid gap-4 md:grid-cols-2">
      <div>
        <label class="mb-1 block text-sm font-medium text-gray-800 dark:text-gray-200">Height (px/vh)</label>
        <input name="height_css" value="{{ old('height_css','70vh') }}" placeholder="es: 70vh oppure 480px"
               class="h-11 w-full rounded-xl border border-[color:var(--accent)] bg-white/90 px-3 outline-none transition
                      hover:border-[color:var(--accent)]/80 focus:ring-2 focus:ring-[color:var(--accent)]
                      dark:bg-black/80 dark:text-white"/>
        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
          Format: <code>70vh</code> or <code>480px</code>
        </p>
        @error('height_css') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
      </div>
      <div class="flex items-end">
        <label class="inline-flex items-center gap-2 text-sm text-gray-800 dark:text-gray-200">
          <input type="checkbox" name="full_bleed" value="1" {{ old('full_bleed', true) ? 'checked' : '' }}
                 class="size-4 rounded border-gray-300 text-[color:var(--accent)] focus:ring-[color:var(--accent)]">
          <span>Full width (edge-to-edge)</span>
        </label>
      </div>
    </div>

    {{-- PAGE SELECT (canonico) --}}
    <div>
      <label class="mb-1 block text-sm font-medium text-gray-800 dark:text-gray-200">Page</label>
      @php
        $pages = [
          'home'          => 'Home',
          'packs'         => 'Packs (list)',
          'packs.show'    => 'Pack (detail)',
          'services'      => 'Services (list)',
          'builders'      => 'Builders (list)',
          'builders.show' => 'Builder (detail)',
          'coaches'       => 'Coaches (list)',
          'coaches.show'  => 'Coach (detail)',
          'about'         => 'About',
          'contacts'      => 'Contacts',
        ];
        $ff = \App\Support\FeatureFlags::all();
        if (!empty($ff['discord_integration'])) {
          $pages['announcements']      = 'Announcements';
          $pages['customers-feedback'] = 'Customers Feedback';
        }
      @endphp
      <select name="page"
              class="h-11 w-full rounded-xl border border-[color:var(--accent)] bg-white/90 px-3 outline-none transition
                     hover:border-[color:var(--accent)]/80 focus:ring-2 focus:ring-[color:var(--accent)]
                     dark:bg-black/80 dark:text-white">
        <option value="">— No item selected —</option>
        @foreach($pages as $key => $label)
          <option value="{{ $key }}" @selected(old('page')===$key)>{{ $label }}</option>
        @endforeach
      </select>
    </div>

    {{-- OVERLAY --}}
    <div>
      <label class="mb-1 block text-sm font-medium text-gray-800 dark:text-gray-200">Overlay (Tailwind class)</label>
      <input name="overlay" value="{{ old('overlay','from-black/20 via-black/10 to-black/50') }}"
             class="h-11 w-full rounded-xl border border-[color:var(--accent)] bg-white/90 px-3 outline-none transition
                    hover:border-[color:var(--accent)]/80 focus:ring-2 focus:ring-[color:var(--accent)]
                    dark:bg-black/80 dark:text-white"/>
    </div>

    {{-- ACTIVE --}}
    <label class="inline-flex items-center gap-2 text-sm text-gray-800 dark:text-gray-200">
      <input type="checkbox" name="is_active" value="1" {{ old('is_active',true) ? 'checked':'' }}
             class="size-4 rounded border-gray-300 text-[color:var(--accent)] focus:ring-[color:var(--accent)]">
      <span>Active</span>
    </label>

    {{-- Actions --}}
    <div class="mt-2 flex items-center gap-3">
      <button class="inline-flex items-center justify-center rounded-xl bg-[color:var(--accent)] px-5 py-2.5 text-white transition hover:opacity-90">
        Save
      </button>
      <a href="{{ route('admin.heroes.index') }}" class="text-sm text-gray-600 hover:underline dark:text-gray-300">Cancel</a>
    </div>
  </form>
</x-admin-layout>