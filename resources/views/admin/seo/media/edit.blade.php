{{-- resources/views/admin/seo/media/edit.blade.php --}}
<x-admin-layout title="Edit Media">
  <div class="mx-auto grid w-full max-w-2xl gap-5 rounded-2xl border border-[color:var(--accent)]/30
              bg-white/70 p-6 shadow-sm backdrop-blur
              dark:border-[color:var(--accent)]/30 dark:bg-gray-900/70">

    {{-- Preview asset --}}
    <div class="flex items-start gap-4">
      <div class="h-28 w-28 overflow-hidden rounded-xl ring-1 ring-black/5 dark:ring-white/10">
        <img src="{{ $mediaAsset->url() }}" class="h-full w-full object-cover" alt="">
      </div>
      <div class="min-w-0">
        <div class="truncate text-sm font-medium">{{ $mediaAsset->path }}</div>
        @if(method_exists($mediaAsset,'size'))
          <div class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">Size: {{ $mediaAsset->size() }}</div>
        @endif
      </div>
    </div>

    {{-- Form --}}
    <form method="POST" action="{{ route('admin.seo.media.update',$mediaAsset) }}" class="grid gap-4">
      @csrf @method('PUT')

      <label class="block">
        <div class="mb-1 text-sm font-medium">Alt text</div>
        <input name="alt_text" value="{{ old('alt_text',$mediaAsset->alt_text) }}"
               class="h-11 w-full rounded-xl border border-[color:var(--accent)]/40 px-3
                      focus:ring-2 focus:ring-[color:var(--accent)]
                      dark:bg-black/70 dark:text-white dark:border-gray-800" />
        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Descrivi l’immagine in modo breve e chiaro.</p>
      </label>

      <label class="inline-flex items-center justify-between gap-4 rounded-xl border border-[color:var(--accent)]/30 px-3 py-2
                     dark:border-gray-800">
        <span class="text-sm">Lazy loading</span>
        <input type="checkbox" name="is_lazy" value="1" @checked($mediaAsset->is_lazy)
               class="peer sr-only">
        <span class="relative inline-flex h-6 w-11 items-center rounded-full bg-gray-300 transition
                     after:absolute after:left-1 after:h-4 after:w-4 after:rounded-full after:bg-white after:transition
                     peer-checked:bg-[color:var(--accent)] peer-checked:after:translate-x-5"></span>
      </label>

      <div class="mt-1 flex items-center gap-3">
        <button class="inline-flex items-center justify-center rounded-xl bg-[color:var(--accent)] px-5 py-2.5 text-white transition
                       hover:opacity-90 active:opacity-80 focus:outline-none focus-visible:ring-2 focus-visible:ring-white/60">
          Save
        </button>
        <a href="{{ route('admin.seo.media.index') }}" class="text-sm text-gray-600 underline dark:text-gray-300">Back</a>
      </div>
    </form>
  </div>
</x-admin-layout>