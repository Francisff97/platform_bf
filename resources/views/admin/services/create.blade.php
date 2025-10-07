<x-admin-layout title="Create Service">
  <x-slot name="header">
    <h1 class="text-xl font-bold">New Service</h1>
  </x-slot>

  <form method="POST"
        action="{{ route('admin.services.store') }}"
        enctype="multipart/form-data"
        class="mx-auto grid w-full max-w-2xl gap-4 rounded-2xl border border-gray-200 bg-white p-5 shadow-sm
               dark:border-gray-700 dark:bg-gray-900">
    @csrf

    {{-- Image + live preview --}}
    <div x-data="{ preview: null }">
      <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Image</label>
      <input type="file" name="image" accept="image/*"
             @change="preview = $event.target.files[0] ? URL.createObjectURL($event.target.files[0]) : null"
             class="w-full rounded border border-gray-300 p-2 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white" />
      @error('image') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror

      <template x-if="preview">
        <img :src="preview" alt="Preview" class="mt-3 h-28 w-full rounded object-cover">
      </template>
    </div>

    {{-- Name --}}
    <div>
      <label class="block text-sm font-medium mb-1">Name</label>
      <input name="name" value="{{ old('name') }}"
             class="w-full rounded border border-gray-300 p-2 dark:border-gray-700 dark:bg-gray-900 dark:text-white" />
      @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>

    {{-- Slug (optional) --}}
    <div>
      <label class="block text-sm font-medium mb-1">Slug (optional)</label>
      <input name="slug" value="{{ old('slug') }}"
             class="w-full rounded border border-gray-300 p-2 dark:border-gray-700 dark:bg-gray-900 dark:text-white" />
      @error('slug') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>

    {{-- Excerpt --}}
    <div>
      <label class="block text-sm font-medium mb-1">Excerpt</label>
      <input name="excerpt" value="{{ old('excerpt') }}"
             class="w-full rounded border border-gray-300 p-2 dark:border-gray-700 dark:bg-gray-900 dark:text-white" />
      @error('excerpt') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>

    {{-- Description --}}
    <div>
      <label class="block text-sm font-medium mb-1">Description</label>
      <textarea name="description" rows="6"
                class="w-full rounded border border-gray-300 p-2 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
      >{{ old('description') }}</textarea>
      @error('description') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>

    {{-- Order + Status --}}
    <div class="grid grid-cols-2 gap-3">
      <div>
        <label class="block text-sm font-medium mb-1">Order</label>
        <input name="order" type="number" value="{{ old('order', 0) }}"
               class="w-full rounded border border-gray-300 p-2 dark:border-gray-700 dark:bg-gray-900 dark:text-white" />
        @error('order') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
      </div>
      <div>
        <label class="block text-sm font-medium mb-1">Status</label>
        <select name="status"
                class="w-full rounded border border-gray-300 p-2 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
          <option value="draft" {{ old('status') === 'draft' ? 'selected' : '' }}>Draft</option>
          <option value="published" {{ old('status','published') === 'published' ? 'selected' : '' }}>Published</option>
        </select>
        @error('status') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
      </div>
    </div>

    <div class="mt-2">
      <button class="rounded bg-[color:var(--accent)] px-4 py-2 text-white hover:opacity-90">Save</button>
      <a href="{{ route('admin.services.index') }}" class="ml-3 text-gray-600 hover:underline dark:text-gray-300">Cancel</a>
    </div>
  </form>
</x-admin-layout>