{{-- resources/views/admin/seo/pages/edit.blade.php --}}
<x-admin-layout title="Edit SEO Page">
  <form method="POST"
        action="{{ route('admin.seo.pages.update',$seoPage) }}"
        enctype="multipart/form-data"
        x-data="{
          mt: '{{ addslashes($seoPage->meta_title ?? '') }}',
          md: '{{ addslashes($seoPage->meta_description ?? '') }}',
          ogPreview: null
        }"
        class="mx-auto grid w-full max-w-3xl gap-5 rounded-2xl border border-[color:var(--accent)]/30
               bg-white/70 p-6 shadow-sm backdrop-blur
               dark:border-[color:var(--accent)]/30 dark:bg-gray-900/70">

    @csrf @method('PUT')

    {{-- Route + Path --}}
    <div class="grid gap-4 md:grid-cols-2">
      <label class="block">
        <div class="mb-1 text-sm font-medium">Route name</div>
        <select name="route_name"
                class="w-full rounded-xl border border-[color:var(--accent)]/40 px-3 py-2
                       focus:ring-2 focus:ring-[color:var(--accent)]
                       dark:bg-black/70 dark:text-white dark:border-gray-800">
          <option value="">—</option>
          @foreach($publicRoutes as $r)
            <option value="{{ $r }}" @selected($seoPage->route_name===$r)>{{ $r }}</option>
          @endforeach
        </select>
      </label>

      <label class="block">
        <div class="mb-1 text-sm font-medium">Path</div>
        <input name="path" value="{{ $seoPage->path }}"
               class="h-11 w-full rounded-xl border border-[color:var(--accent)]/40 px-3
                      focus:ring-2 focus:ring-[color:var(--accent)]
                      dark:bg-black/70 dark:text-white dark:border-gray-800" />
      </label>
    </div>

    {{-- Meta title --}}
    <label class="block">
      <div class="mb-1 flex items-center justify-between text-sm">
        <span class="font-medium">Meta title</span>
        <span class="text-xs text-gray-500 dark:text-gray-400">
          <span x-text="mt.length"></span>/60
        </span>
      </div>
      <input name="meta_title" x-model="mt"
             class="h-11 w-full rounded-xl border border-[color:var(--accent)]/40 px-3
                    focus:ring-2 focus:ring-[color:var(--accent)]
                    dark:bg-black/70 dark:text-white dark:border-gray-800" />
      <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Consiglio: ≤ 60 caratteri.</p>
    </label>

    {{-- Meta description --}}
    <label class="block">
      <div class="mb-1 flex items-center justify-between text-sm">
        <span class="font-medium">Meta description</span>
        <span class="text-xs text-gray-500 dark:text-gray-400">
          <span x-text="md.length"></span>/160
        </span>
      </div>
      <textarea name="meta_description" x-model="md" rows="4"
                class="w-full rounded-xl border border-[color:var(--accent)]/40 px-3 py-2
                       focus:ring-2 focus:ring-[color:var(--accent)]
                       dark:bg-black/70 dark:text-white dark:border-gray-800"></textarea>
      <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Consiglio: 150–160 caratteri.</p>
    </label>

    {{-- OpenGraph image --}}
    <div class="grid gap-4 md:grid-cols-[auto,1fr] md:items-start">
      <div class="flex flex-col items-start gap-2">
        <div class="text-sm font-medium">OpenGraph image</div>

        {{-- Preview attuale / nuova --}}
        <div class="relative h-24 w-40 overflow-hidden rounded-xl ring-1 ring-black/5 dark:ring-white/10">
          @if($seoPage->og_image_path)
            <img src="{{ Storage::url($seoPage->og_image_path) }}"
                 alt="Current OG" class="h-full w-full object-cover" x-show="!ogPreview">
          @else
            <div class="grid h-full w-full place-items-center text-xs text-gray-400" x-show="!ogPreview">No image</div>
          @endif
          <img :src="ogPreview" alt="" class="hidden h-full w-full object-cover" x-bind:class="{'hidden': !ogPreview}">
        </div>
      </div>

      <label class="block">
        <div class="sr-only">Upload</div>
        <input type="file" name="og_image" accept="image/*"
               @change="ogPreview = $event.target.files?.[0] ? URL.createObjectURL($event.target.files[0]) : null"
               class="w-full cursor-pointer rounded-xl border border-[color:var(--accent)]/40 px-3 py-2
                      file:mr-3 file:rounded-lg file:border-0 file:bg-[color:var(--accent)] file:px-3 file:py-2 file:text-white
                      dark:bg-black/70 dark:text-white dark:border-gray-800" />
        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Formato consigliato 1200×630 (JPG/PNG/WebP).</p>
      </label>
    </div>

    <div class="mt-2 flex items-center gap-3">
      <button class="inline-flex items-center justify-center rounded-xl bg-[color:var(--accent)] px-5 py-2.5 text-white transition
                     hover:opacity-90 active:opacity-80 focus:outline-none focus-visible:ring-2 focus-visible:ring-white/60">
        Update
      </button>
      <a href="{{ route('admin.seo.pages.index') }}" class="text-sm text-gray-600 underline dark:text-gray-300">Cancel</a>
    </div>
  </form>
</x-admin-layout>