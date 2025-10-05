<x-admin-layout :title="$section->exists ? 'Edit About Section' : 'Add About Section'">
  <form method="POST"
        action="{{ $section->exists ? route('admin.about.update', $section) : route('admin.about.store') }}"
        enctype="multipart/form-data"
        class="max-w-3xl grid gap-5">
    @csrf
    @if($section->exists) @method('PUT') @endif

    <div class="rounded border p-4 dark:border-gray-800 bg-white/70 dark:bg-gray-900/60">
      <label class="block text-sm font-medium mb-1">Layout</label>
      @php $layout = old('layout', $section->layout ?? 'text'); @endphp
      <select name="layout" class="w-full rounded border px-3 py-2 dark:bg-gray-900">
        <option value="text"        @selected($layout==='text')>Text only</option>
        <option value="image_left"  @selected($layout==='image_left')>Image left, text right</option>
        <option value="image_right" @selected($layout==='image_right')>Image right, text left</option>
        <option value="hero"        @selected($layout==='hero')>Hero banner</option>
      </select>
    </div>

    <div class="rounded border p-4 dark:border-gray-800 bg-white/70 dark:bg-gray-900/60">
      <label class="block text-sm font-medium mb-1">Title</label>
      <input type="text" name="title" value="{{ old('title', $section->title) }}"
             class="w-full rounded border px-3 py-2 dark:bg-gray-900" placeholder="Section title">
    </div>

    <div class="rounded border p-4 dark:border-gray-800 bg-white/70 dark:bg-gray-900/60">
      <label class="block text-sm font-medium mb-1">Body</label>
      <textarea name="body" rows="6" class="w-full rounded border px-3 py-2 dark:bg-gray-900"
                placeholder="Write your content...">{{ old('body', $section->body) }}</textarea>
    </div>

    {{-- Modern image uploader with preview --}}
    <div class="rounded border p-4 dark:border-gray-800 bg-white/70 dark:bg-gray-900/60">
      <label class="block text-sm font-medium mb-2">Image</label>

      <div class="flex items-center gap-4">
        <div class="relative h-28 w-28 overflow-hidden rounded-lg border dark:border-gray-700 bg-gray-50 dark:bg-gray-800">
          <img id="previewImg"
               src="{{ $section->image_path ? Storage::url($section->image_path) : '' }}"
               alt=""
               class="h-full w-full object-cover {{ $section->image_path ? '' : 'hidden' }}">
          <div id="placeholder"
               class="absolute inset-0 flex items-center justify-center text-xs text-gray-500 {{ $section->image_path ? 'hidden' : '' }}">
            No image
          </div>
        </div>

        <div>
          <label for="imageInput"
                 class="inline-flex cursor-pointer items-center gap-2 rounded-lg border px-3 py-2 text-sm hover:bg-gray-50 dark:hover:bg-gray-800 dark:border-gray-700">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path d="M12 5v14m7-7H5"/></svg>
            Upload image
          </label>
          <input id="imageInput" type="file" name="image" accept="image/*" class="hidden">
          @if($section->image_path)
            <div class="mt-1 text-xs text-gray-500">Current: {{ $section->image_path }}</div>
          @endif
        </div>
      </div>
      <script>
        document.addEventListener('DOMContentLoaded', () => {
          const input = document.getElementById('imageInput');
          const img   = document.getElementById('previewImg');
          const ph    = document.getElementById('placeholder');

          input?.addEventListener('change', e => {
            const [file] = input.files || [];
            if (!file) return;
            const url = URL.createObjectURL(file);
            img.src = url;
            img.classList.remove('hidden');
            ph.classList.add('hidden');
          });
        });
      </script>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
      <div class="rounded border p-4 dark:border-gray-800 bg-white/70 dark:bg-gray-900/60">
        <label class="block text-sm font-medium mb-1">Position</label>
        <input type="number" name="position" min="1"
               value="{{ old('position', $section->position ?? ((\App\Models\AboutSection::max('position') ?? 0)+1)) }}"
               class="w-full rounded border px-3 py-2 dark:bg-gray-900">
      </div>

      <label class="rounded border p-4 dark:border-gray-800 bg-white/70 dark:bg-gray-900/60 flex items-center gap-2">
        <input type="checkbox" name="featured" value="1" @checked(old('featured', $section->featured))>
        <span class="text-sm">Featured</span>
      </label>

      {{-- Usa il nome 'is_active' (mappo anche 'published' nel controller) --}}
      <label class="rounded border p-4 dark:border-gray-800 bg-white/70 dark:bg-gray-900/60 flex items-center gap-2">
        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $section->is_active))>
        <span class="text-sm">Active (visible)</span>
      </label>
    </div>

    <div class="flex items-center gap-3">
      <button class="rounded bg-[var(--accent)] px-4 py-2 text-white">
        {{ $section->exists ? 'Update' : 'Create' }}
      </button>
      <a href="{{ route('admin.about.index') }}" class="underline">Cancel</a>
    </div>
  </form>
</x-admin-layout>