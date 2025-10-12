<x-admin-layout title="Add slide">
  {{-- Errori --}}
  @if ($errors->any())
    <div class="mb-4 rounded-xl border border-red-300 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-900/40 dark:bg-red-900/20 dark:text-red-200">
      <div class="mb-1 font-semibold">Correct these errors:</div>
      <ul class="list-disc pl-5 space-y-0.5">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
  @endif

  <form method="POST" action="{{ route('admin.slides.store') }}" enctype="multipart/form-data"
        class="mx-auto grid w-full max-w-3xl gap-5">
    @csrf

    {{-- Card: contenuti base --}}
    <div class="rounded-2xl border bg-white/70 p-5 shadow-sm ring-1 ring-black/5 backdrop-blur
                dark:border-gray-800 dark:bg-gray-900/70 dark:ring-white/10">
      <div class="grid gap-4 md:grid-cols-2">
        <label class="block">
          <div class="mb-1 text-sm font-medium text-gray-700 dark:text-gray-200">Title</div>
          <input name="title" value="{{ old('title') }}"
                 class="h-11 w-full rounded-xl border px-3 dark:border-gray-700 dark:bg-gray-900 dark:text-white" />
        </label>
        <label class="block">
          <div class="mb-1 text-sm font-medium text-gray-700 dark:text-gray-200">Subtitle</div>
          <input name="subtitle" value="{{ old('subtitle') }}"
                 class="h-11 w-full rounded-xl border px-3 dark:border-gray-700 dark:bg-gray-900 dark:text-white" />
        </label>
      </div>

      <div class="mt-4 grid gap-4 md:grid-cols-2">
        <label class="block">
          <div class="mb-1 text-sm font-medium text-gray-700 dark:text-gray-200">CTA label</div>
          <input name="cta_label" value="{{ old('cta_label') }}" placeholder="Es. Discover"
                 class="h-11 w-full rounded-xl border px-3 dark:border-gray-700 dark:bg-gray-900 dark:text-white" />
        </label>
        <label class="block">
          <div class="mb-1 text-sm font-medium text-gray-700 dark:text-gray-200">CTA URL</div>
          <input name="cta_url" value="{{ old('cta_url') }}" placeholder="https://…"
                 class="h-11 w-full rounded-xl border px-3 dark:border-gray-700 dark:bg-gray-900 dark:text-white" />
        </label>
      </div>
    </div>

    {{-- Card: upload + preview live --}}
    <div class="rounded-2xl border bg-white/70 p-5 shadow-sm ring-1 ring-black/5 backdrop-blur
                dark:border-gray-800 dark:bg-gray-900/70 dark:ring-white/10"
         x-data="{ preview:null }">
      <div class="flex flex-col gap-4 md:flex-row md:items-center">
        <div class="w-full md:w-1/2">
          <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-200">
            Image <span class="text-rose-600">*</span>
          </label>
          <input type="file" name="image" accept="image/*" required
                 @change="preview = $event.target.files?.[0] ? URL.createObjectURL($event.target.files[0]) : null"
                 class="w-full rounded-xl border px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white file:mr-3 file:rounded-lg file:border-0 file:bg-[color:var(--accent)] file:px-3 file:py-2 file:text-white" />
          <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">JPG/PNG/WebP, max 8MB</p>
        </div>
        <x-admin.image-hint field="hero"/>
        <div class="w-full md:w-1/2">
          <div class="mb-1 text-sm font-medium text-gray-700 dark:text-gray-200">Preview</div>
          <div class="relative aspect-[16/9] w-full overflow-hidden rounded-xl ring-1 ring-black/5 dark:ring-white/10">
            <img x-show="preview" :src="preview" class="h-full w-full object-cover" alt="">
            <div x-show="!preview" class="grid h-full place-items-center text-xs text-gray-400 dark:text-gray-500">No image</div>

            {{-- mock CTA sulla preview --}}
            <div x-show="preview"
                 class="pointer-events-none absolute inset-0 bg-gradient-to-b from-black/40 via-black/20 to-black/40"></div>
            <div x-show="preview"
                 class="pointer-events-none absolute bottom-3 left-3 inline-flex items-center gap-2 rounded-full bg-[var(--accent)] px-3 py-1.5 text-xs font-medium text-white">
              CTA
              <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
            </div>
          </div>
        </div>
      </div>
    </div>

    {{-- Card: meta --}}
    <div class="rounded-2xl border bg-white/70 p-5 shadow-sm ring-1 ring-black/5 backdrop-blur
                dark:border-gray-800 dark:bg-gray-900/70 dark:ring-white/10">
      <div class="grid gap-4 md:grid-cols-2">
        <label class="block">
          <div class="mb-1 text-sm font-medium text-gray-700 dark:text-gray-200">Order</div>
          <input type="number" name="sort_order" value="{{ old('sort_order',0) }}"
                 class="h-11 w-full rounded-xl border px-3 dark:border-gray-700 dark:bg-gray-900 dark:text-white" />
        </label>
        <div class="flex items-end">
          <label class="inline-flex items-center gap-2">
            <input type="checkbox" name="is_active" value="1" checked
                   class="h-4 w-4 rounded border-gray-300 text-[color:var(--accent)] focus:ring-[color:var(--accent)] dark:border-gray-700">
            <span class="text-sm text-gray-700 dark:text-gray-200">Active</span>
          </label>
        </div>
      </div>
    </div>

    {{-- Actions --}}
    <div class="flex items-center gap-3">
      <button class="inline-flex items-center rounded-xl bg-[color:var(--accent)] px-4 py-2.5 text-white hover:opacity-90">Save</button>
      <a href="{{ route('admin.slides.index') }}" class="text-sm text-gray-600 underline hover:opacity-80 dark:text-gray-300">Cancel</a>
    </div>
  </form>
</x-admin-layout>