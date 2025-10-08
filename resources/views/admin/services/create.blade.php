<x-admin-layout title="Create Service">
  <x-slot name="header">
    <h1 class="text-xl font-bold">New Service</h1>
  </x-slot>

  <form method="POST"
        action="{{ route('admin.services.store') }}"
        enctype="multipart/form-data"
        class="mx-auto grid w-full max-w-2xl gap-5 rounded-2xl border border-[color:var(--accent)]/30 bg-white/70 p-6 shadow-sm backdrop-blur
               dark:border-[color:var(--accent)]/30 dark:bg-gray-900/70">

    @csrf

    {{-- Image (usa il tuo metodo: name="image") --}}
    <div x-data="{ preview: null }">
      <label class="mb-1 block text-sm font-medium text-gray-800 dark:text-gray-200">Image</label>
      <input type="file" name="image" accept="image/*"
             @change="preview = $event.target.files?.[0] ? URL.createObjectURL($event.target.files[0]) : null"
             class="w-full rounded-xl border border-[color:var(--accent)] bg-white/90 p-2 text-sm text-black outline-none transition
                    file:mr-3 file:rounded-lg file:border-0 file:bg-[color:var(--accent)] file:px-3 file:py-2 file:text-white
                    hover:border-[color:var(--accent)]/80 focus:ring-2 focus:ring-[color:var(--accent)]
                    dark:bg-black/80 dark:text-white" />
      @error('image') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror

      {{-- preview live (opzionale; non influisce sul salvataggio) --}}
      <template x-if="preview">
        <img :src="preview" alt="Preview"
             class="mt-3 h-36 w-full rounded-lg object-cover ring-1 ring-black/5 dark:ring-white/10" />
      </template>
    </div>

    {{-- Name --}}
    <div>
      <label class="mb-1 block text-sm font-medium text-gray-800 dark:text-gray-200">Name</label>
      <input name="name" placeholder="Service name" value="{{ old('name') }}"
             class="h-11 w-full rounded-xl border border-[color:var(--accent)] bg-white/90 px-3 text-black placeholder:text-gray-500 outline-none transition
                    hover:border-[color:var(--accent)]/80 focus:ring-2 focus:ring-[color:var(--accent)]
                    dark:bg-black/80 dark:text-white dark:placeholder:text-gray-400" />
      @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>

    {{-- Slug (optional) --}}
    <div>
      <label class="mb-1 block text-sm font-medium text-gray-800 dark:text-gray-200">Slug (optional)</label>
      <input name="slug" placeholder="auto or custom" value="{{ old('slug') }}"
             class="h-11 w-full rounded-xl border border-[color:var(--accent)] bg-white/90 px-3 text-black placeholder:text-gray-500 outline-none transition
                    hover:border-[color:var(--accent)]/80 focus:ring-2 focus:ring-[color:var(--accent)]
                    dark:bg-black/80 dark:text-white dark:placeholder:text-gray-400" />
      @error('slug') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>

    {{-- Excerpt --}}
    <div>
      <label class="mb-1 block text-sm font-medium text-gray-800 dark:text-gray-200">Excerpt</label>
      <input name="excerpt" placeholder="Short teaser" value="{{ old('excerpt') }}"
             class="h-11 w-full rounded-xl border border-[color:var(--accent)] bg-white/90 px-3 text-black placeholder:text-gray-500 outline-none transition
                    hover:border-[color:var(--accent)]/80 focus:ring-2 focus:ring-[color:var(--accent)]
                    dark:bg-black/80 dark:text-white dark:placeholder:text-gray-400" />
      @error('excerpt') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>

    {{-- Body / Description (allineato alla tua edit) --}}
    <div>
      <label class="mb-1 block text-sm font-medium text-gray-800 dark:text-gray-200">Description</label>
      <textarea name="body" rows="6" placeholder="Full description"
                class="w-full rounded-xl border border-[color:var(--accent)] bg-white/90 p-3 text-black placeholder:text-gray-500 outline-none transition
                       hover:border-[color:var(--accent)]/80 focus:ring-2 focus:ring-[color:var(--accent)]
                       dark:bg-black/80 dark:text-white dark:placeholder:text-gray-400">{{ old('body') }}</textarea>
      @error('body') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>

    {{-- Order + Status --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
      <div>
        <label class="mb-1 block text-sm font-medium text-gray-800 dark:text-gray-200">Order</label>
        <input name="order" type="number" value="{{ old('order', 0) }}"
               class="h-11 w-full rounded-xl border border-[color:var(--accent)] bg-white/90 px-3 text-black outline-none transition
                      hover:border-[color:var(--accent)]/80 focus:ring-2 focus:ring-[color:var(--accent)]
                      dark:bg-black/80 dark:text-white" />
        @error('order') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
      </div>
      <div>
        <label class="mb-1 block text-sm font-medium text-gray-800 dark:text-gray-200">Status</label>
        <select name="status"
                class="h-11 w-full rounded-xl border border-[color:var(--accent)] bg-white/90 px-3 text-black outline-none transition
                       hover:border-[color:var(--accent)]/80 focus:ring-2 focus:ring-[color:var(--accent)]
                       dark:bg-black/80 dark:text-white">
          <option value="draft" {{ old('status') === 'draft' ? 'selected' : '' }}>Draft</option>
          <option value="published" {{ old('status','published') === 'published' ? 'selected' : '' }}>Published</option>
        </select>
        @error('status') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
      </div>
    </div>

    {{-- Actions --}}
    <div class="mt-2 flex items-center gap-3">
      <button class="inline-flex items-center justify-center rounded-xl bg-[color:var(--accent)] px-5 py-2.5 text-white transition
                     hover:opacity-90 active:opacity-80 focus:outline-none focus-visible:ring-2 focus-visible:ring-white/60">
        Save
      </button>
      <a href="{{ route('admin.services.index') }}" class="text-gray-600 hover:underline dark:text-gray-300">Cancel</a>
    </div>
  </form>
</x-admin-layout>
