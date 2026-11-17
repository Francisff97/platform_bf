<x-admin-layout title="About – Create section">
  {{-- errori globali --}}
  @if ($errors->any())
    <div class="mb-4 rounded-xl border border-rose-300 bg-rose-50 px-4 py-3 text-sm text-rose-800 dark:border-rose-800 dark:bg-rose-950/40 dark:text-rose-200">
      <div class="mb-1 font-semibold">Please fix these errors:</div>
      <ul class="list-disc pl-5">
        @foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
      </ul>
    </div>
  @endif

  <form method="POST"
        action="{{ route('admin.about.store') }}"
        enctype="multipart/form-data"
        class="mx-auto grid w-full max-w-3xl gap-5">
    @csrf

    {{-- Layout + Position --}}
    <div class="grid gap-4 sm:grid-cols-2">
      <div class="rounded-xl border bg-white/70 p-4 dark:border-gray-800 dark:bg-gray-900/60">
        <label class="mb-1 block text-sm font-medium">Layout</label>
        <select name="layout" class="w-full rounded-lg border px-3 py-2 dark:border-gray-800 dark:bg-gray-900">
          @foreach(['text'=>'Text only','image_left'=>'Image left','image_right'=>'Image right','hero'=>'Hero banner'] as $k=>$lbl)
            <option value="{{ $k }}" @selected(old('layout','text')===$k)>{{ $lbl }}</option>
          @endforeach
        </select>
      </div>
      <div class="rounded-xl border bg-white/70 p-4 dark:border-gray-800 dark:bg-gray-900/60">
        <label class="mb-1 block text-sm font-medium">Position</label>
        <input type="number" name="position"
               value="{{ old('position', (\App\Models\AboutSection::max('position') ?? 0) + 1) }}"
               class="w-full rounded-lg border px-3 py-2 dark:border-gray-800 dark:bg-gray-900">
      </div>
    </div>

    {{-- Title --}}
    <div class="rounded-xl border bg-white/70 p-4 dark:border-gray-800 dark:bg-gray-900/60">
      <label class="mb-1 block text-sm font-medium">Title</label>
      <input name="title" value="{{ old('title') }}" placeholder="Section title"
             class="w-full rounded-lg border px-3 py-2 dark:border-gray-800 dark:bg-gray-900">
    </div>

    {{-- Body --}}
    <div class="rounded-xl border bg-white/70 p-4 dark:border-gray-800 dark:bg-gray-900/60">
      <label class="mb-1 block text-sm font-medium">Body</label>
      <textarea name="body" rows="6" placeholder="Write your content…"
                class="w-full rounded-lg border px-3 py-2 dark:border-gray-800 dark:bg-gray-900">{{ old('body') }}</textarea>
    </div>

    {{-- Image uploader + preview --}}
    <div class="rounded-xl border bg-white/70 p-4 dark:border-gray-800 dark:bg-gray-900/60">
      <label class="mb-2 block text-sm font-medium">Image</label>
      <div class="flex items-center gap-4" x-data="{p:null}">
        <div class="relative h-28 w-28 overflow-hidden rounded-lg border bg-gray-50 dark:border-gray-700 dark:bg-gray-800">
          <img x-show="p" :src="p" class="h-full w-full object-cover" alt="">
          <div x-show="!p" class="absolute inset-0 grid place-items-center text-xs text-gray-500">No image</div>
        </div>
        <div>
          <label class="inline-flex cursor-pointer items-center gap-2 rounded-lg border px-3 py-2 text-sm hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path d="M12 5v14m7-7H5"/></svg>
            Upload image
            <input type="file" name="image" accept="image/*" class="hidden"
                   @change="p = $event.target.files?.[0] ? URL.createObjectURL($event.target.files[0]) : null">
          </label>
          <p class="mt-1 text-xs text-gray-500">JPG/PNG/WebP, consigliato orizzontale.</p>
        </div>
      </div>
      @error('image')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
    </div>
    <x-admin.image-hint field="card"/>
    {{-- Flags --}}
    <div class="grid gap-4 sm:grid-cols-2">
      <label class="inline-flex items-center justify-between gap-3 rounded-xl border bg-white/70 px-4 py-3 dark:border-gray-800 dark:bg-gray-900/60">
        <span class="text-sm">Featured (homepage)</span>
        <input type="checkbox" name="featured" value="1" class="h-4 w-4 rounded border-gray-300"
               {{ old('featured') ? 'checked' : '' }}>
      </label>
      <label class="inline-flex items-center justify-between gap-3 rounded-xl border bg-white/70 px-4 py-3 dark:border-gray-800 dark:bg-gray-900/60">
        <span class="text-sm">Active (visible)</span>
        <input type="checkbox" name="is_active" value="1" class="h-4 w-4 rounded border-gray-300"
               {{ old('is_active', true) ? 'checked' : '' }}>
      </label>
    </div>

    <div class="pt-2 flex items-center gap-3">
      <button class="rounded-xl bg-[var(--accent)] px-4 py-2 text-white hover:opacity-90">Create</button>
      <a href="{{ route('admin.about.index') }}" class="text-sm underline">Cancel</a>
    </div>
  </form>
</x-admin-layout>