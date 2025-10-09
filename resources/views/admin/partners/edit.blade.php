<x-admin-layout title="Edit Partner">
  <div class="mb-4 flex items-center justify-between">
    <h1 class="text-lg font-semibold">Edit Partner</h1>
    <a href="{{ route('admin.partners.index') }}" class="text-sm underline">Back to list</a>
  </div>

  @if ($errors->any())
    <div class="mb-4 rounded-xl border border-red-300 bg-red-50/80 p-3 text-sm text-red-700">
      <div class="mb-1 font-semibold">Please fix the following:</div>
      <ul class="list-disc pl-5">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
  @endif

  <form method="POST" action="{{ route('admin.partners.update',$partner) }}" enctype="multipart/form-data"
        class="mx-auto grid w-full max-w-xl gap-5 rounded-2xl border border-[color:var(--accent)]/30 bg-white/70 p-6 shadow-sm backdrop-blur
               dark:border-gray-800 dark:bg-gray-900/70">
    @csrf @method('PUT')

    {{-- Name --}}
    <div>
      <label class="mb-1 block text-sm font-medium text-gray-800 dark:text-gray-200">Name</label>
      <input name="name" value="{{ old('name',$partner->name) }}"
             class="h-11 w-full rounded-xl border border-[color:var(--accent)]/60 bg-white/90 px-3 text-black placeholder:text-gray-500 outline-none transition
                    hover:border-[color:var(--accent)] focus:ring-2 focus:ring-[color:var(--accent)]
                    dark:bg-black/70 dark:text-white dark:placeholder:text-gray-400" required>
      @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>

    {{-- URL --}}
    <div>
      <label class="mb-1 block text-sm font-medium text-gray-800 dark:text-gray-200">URL (optional)</label>
      <input name="url" value="{{ old('url',$partner->url) }}" placeholder="https://example.com"
             class="h-11 w-full rounded-xl border border-[color:var(--accent)]/60 bg-white/90 px-3 text-black placeholder:text-gray-500 outline-none transition
                    hover:border-[color:var(--accent)] focus:ring-2 focus:ring-[color:var(--accent)]
                    dark:bg-black/70 dark:text-white dark:placeholder:text-gray-400">
      @error('url')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>

    {{-- Current logo + remove --}}
    <div>
      <label class="mb-1 block text-sm font-medium text-gray-800 dark:text-gray-200">Current logo</label>
      <div class="flex items-center gap-3">
        <div class="h-20 w-20 overflow-hidden rounded-full ring-1 ring-black/5 dark:ring-white/10 bg-gray-100 dark:bg-gray-800">
          @if($partner->logo_path)
            <img src="{{ Storage::url($partner->logo_path) }}" class="h-full w-full object-cover" alt="{{ $partner->name }}">
          @endif
        </div>
        <label class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
          <input type="checkbox" name="remove_logo" value="1" class="rounded">
          <span>Remove logo</span>
        </label>
      </div>
    </div>

    {{-- Upload new + live preview --}}
    <div x-data="{ preview: null }">
      <label class="mb-1 block text-sm font-medium text-gray-800 dark:text-gray-200">Upload new logo</label>
      <input type="file" name="logo" accept="image/*"
             @change="preview = $event.target.files?.[0] ? URL.createObjectURL($event.target.files[0]) : null"
             class="w-full rounded-xl border border-[color:var(--accent)]/60 bg-white/90 p-2 text-sm text-black outline-none transition
                    file:mr-3 file:rounded-lg file:border-0 file:bg-[color:var(--accent)] file:px-3 file:py-2 file:text-white
                    hover:border-[color:var(--accent)] focus:ring-2 focus:ring-[color:var(--accent)]
                    dark:bg-black/70 dark:text-white">
      @error('logo')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror

      <template x-if="preview">
        <img :src="preview" alt="Preview"
             class="mt-3 h-24 w-24 rounded-full object-cover ring-1 ring-black/5 dark:ring-white/10" />
      </template>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
      {{-- Order --}}
      <div>
        <label class="mb-1 block text-sm font-medium text-gray-800 dark:text-gray-200">Order</label>
        <input type="number" name="order" value="{{ old('order',$partner->order) }}"
               class="h-11 w-full rounded-xl border border-[color:var(--accent)]/60 bg-white/90 px-3 text-black outline-none transition
                      hover:border-[color:var(--accent)] focus:ring-2 focus:ring-[color:var(--accent)]
                      dark:bg-black/70 dark:text-white">
      </div>

      {{-- Status --}}
      <div>
        <label class="mb-1 block text-sm font-medium text-gray-800 dark:text-gray-200">Status</label>
        <select name="status"
                class="h-11 w-full rounded-xl border border-[color:var(--accent)]/60 bg-white/90 px-3 text-black outline-none transition
                       hover:border-[color:var(--accent)] focus:ring-2 focus:ring-[color:var(--accent)]
                       dark:bg-black/70 dark:text-white">
          <option value="draft" @selected(old('status',$partner->status)==='draft')>Draft</option>
          <option value="published" @selected(old('status',$partner->status)==='published')>Published</option>
        </select>
      </div>
    </div>

    {{-- Actions --}}
    <div class="mt-1 flex items-center gap-3">
      <button class="inline-flex items-center justify-center rounded-xl bg-[color:var(--accent)] px-5 py-2.5 text-white transition
                     hover:opacity-90 active:opacity-80 focus:outline-none focus-visible:ring-2 focus-visible:ring-white/60">
        Update
      </button>
      <a href="{{ route('admin.partners.index') }}" class="text-gray-600 hover:underline dark:text-gray-300">Cancel</a>
    </div>
  </form>
</x-admin-layout>