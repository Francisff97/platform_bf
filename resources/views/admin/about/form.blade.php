<x-admin-layout :title="$section->exists ? 'Edit About Section' : 'Add About Section'">
  <form method="POST"
        action="{{ $section->exists ? route('admin.about.update', $section) : route('admin.about.store') }}"
        enctype="multipart/form-data"
        class="mx-auto grid w-full max-w-3xl gap-5">
    @csrf
    @if($section->exists) @method('PUT') @endif

    {{-- Layout --}}
    <div class="rounded-xl border bg-white/70 p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900/60">
      <label class="mb-1 block text-sm font-medium text-gray-800 dark:text-gray-200">Layout</label>
      @php $layout = old('layout', $section->layout ?? 'text'); @endphp
      <select name="layout"
              class="w-full rounded-lg border border-gray-200 px-3 py-2 outline-none transition
                     focus:ring-2 focus:ring-[color:var(--accent)] dark:border-gray-800 dark:bg-gray-900">
        <option value="text"        @selected($layout==='text')>Text only</option>
        <option value="image_left"  @selected($layout==='image_left')>Image left, text right</option>
        <option value="image_right" @selected($layout==='image_right')>Image right, text left</option>
        <option value="hero"        @selected($layout==='hero')>Hero banner</option>
      </select>
    </div>

    {{-- Title --}}
    <div class="rounded-xl border bg-white/70 p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900/60">
      <label class="mb-1 block text-sm font-medium text-gray-800 dark:text-gray-200">Title</label>
      <input type="text" name="title" value="{{ old('title', $section->title) }}"
             class="w-full rounded-lg border border-gray-200 px-3 py-2 outline-none transition
                    focus:ring-2 focus:ring-[color:var(--accent)] dark:border-gray-800 dark:bg-gray-900"
             placeholder="Section title">
    </div>

    {{-- Body --}}
    <div class="rounded-xl border bg-white/70 p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900/60">
      <label class="mb-1 block text-sm font-medium text-gray-800 dark:text-gray-200">Body</label>
      <textarea name="body" rows="6"
                class="w-full rounded-lg border border-gray-200 px-3 py-2 outline-none transition
                       focus:ring-2 focus:ring-[color:var(--accent)] dark:border-gray-800 dark:bg-gray-900"
                placeholder="Write your content...">{{ old('body', $section->body) }}</textarea>
    </div>

    {{-- Image uploader + live preview --}}
    <div class="rounded-xl border bg-white/70 p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900/60">
      <label class="mb-2 block text-sm font-medium text-gray-800 dark:text-gray-200">Image</label>

      <div class="flex items-center gap-4">
        <div class="relative h-28 w-28 overflow-hidden rounded-lg border bg-gray-50 ring-1 ring-black/5
                    dark:border-gray-700 dark:bg-gray-800 dark:ring-white/10">
          <img id="previewImg"
               src="{{ $section->image_path ? Storage::url($section->image_path) : '' }}"
               alt=""
               class="h-full w-full object-cover {{ $section->image_path ? '' : 'hidden' }}">
          <div id="placeholder"
               class="absolute inset-0 grid place-items-center text-xs text-gray-500 {{ $section->image_path ? 'hidden' : '' }}">
            No image
          </div>
        </div>

        <div class="space-y-1">
          <label for="imageInput"
                 class="inline-flex cursor-pointer items-center gap-2 rounded-lg border px-3 py-2 text-sm
                        hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path d="M12 5v14m7-7H5"/></svg>
            Upload image
          </label>
          <input id="imageInput" type="file" name="image" accept="image/*" class="hidden">
          @if($section->image_path)
            <div class="text-xs text-gray-500">Current: {{ $section->image_path }}</div>
          @endif
        </div>
      </div>
       <x-admin.image-hint field="card"/>
      <script>
        document.addEventListener('DOMContentLoaded', () => {
          const input = document.getElementById('imageInput');
          const img   = document.getElementById('previewImg');
          const ph    = document.getElementById('placeholder');
          input?.addEventListener('change', () => {
            const file = input.files && input.files[0];
            if (!file) return;
            const url = URL.createObjectURL(file);
            img.src = url; img.classList.remove('hidden'); ph.classList.add('hidden');
          });
        });
      </script>
    </div>

    {{-- Position + Flags --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
      <div class="rounded-xl border bg-white/70 p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900/60">
        <label class="mb-1 block text-sm font-medium text-gray-800 dark:text-gray-200">Position</label>
        <input type="number" name="position" min="1"
               value="{{ old('position', $section->position ?? ((\App\Models\AboutSection::max('position') ?? 0)+1)) }}"
               class="w-full rounded-lg border border-gray-200 px-3 py-2 outline-none transition
                      focus:ring-2 focus:ring-[color:var(--accent)] dark:border-gray-800 dark:bg-gray-900">
      </div>

      <label class="flex items-center justify-between gap-3 rounded-xl border bg-white/70 px-4 py-3 text-sm shadow-sm
                    dark:border-gray-800 dark:bg-gray-900/60">
        <span>Featured</span>
        <input type="checkbox" name="featured" value="1"
               class="h-4 w-4 rounded border-gray-300 dark:border-gray-600"
               @checked(old('featured', $section->featured))>
      </label>

      {{-- usa 'is_active' --}}
      <label class="flex items-center justify-between gap-3 rounded-xl border bg-white/70 px-4 py-3 text-sm shadow-sm
                    dark:border-gray-800 dark:bg-gray-900/60">
        <span>Active (visible)</span>
        <input type="checkbox" name="is_active" value="1"
               class="h-4 w-4 rounded border-gray-300 dark:border-gray-600"
               @checked(old('is_active', $section->is_active))>
      </label>
    </div>

    {{-- Actions --}}
    <div class="flex items-center gap-3 pt-1">
      <button class="rounded-xl bg-[color:var(--accent)] px-4 py-2 text-white transition hover:opacity-90">
        {{ $section->exists ? 'Update' : 'Create' }}
      </button>
      <a href="{{ route('admin.about.index') }}" class="text-sm underline">Cancel</a>
    </div>
  </form>
</x-admin-layout>