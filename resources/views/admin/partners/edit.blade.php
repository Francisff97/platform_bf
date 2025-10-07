<x-admin-layout title="Edit Partner">
  <h1 class="mb-4 text-xl font-bold">Edit Partner</h1>

  <form method="POST" action="{{ route('admin.partners.update',$partner) }}" enctype="multipart/form-data"
        class="grid max-w-xl gap-4 rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900">
    @csrf @method('PUT')

    <div>
      <label class="mb-1 block text-sm font-medium">Name</label>
      <input name="name" value="{{ old('name',$partner->name) }}" class="w-full rounded border p-2 dark:bg-gray-900 dark:text-white">
      @error('name')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
    </div>

    <div>
      <label class="mb-1 block text-sm font-medium">URL (optional)</label>
      <input name="url" value="{{ old('url',$partner->url) }}" class="w-full rounded border p-2 dark:bg-gray-900 dark:text-white">
      @error('url')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
    </div>

    <div>
      <label class="mb-1 block text-sm font-medium">Current Logo</label>
      <div class="flex items-center gap-3">
        <div class="h-24 w-24 overflow-hidden rounded-full ring-1 ring-black/5 dark:ring-white/10">
          @if($partner->logo_path)
            <img src="{{ Storage::url($partner->logo_path) }}" class="h-full w-full object-cover" alt="{{ $partner->name }}">
          @else
            <div class="grid h-full w-full place-items-center text-xs text-gray-400">No logo</div>
          @endif
        </div>
        <label class="flex items-center gap-2 text-sm">
          <input type="checkbox" name="remove_logo" value="1" class="rounded">
          Remove logo
        </label>
      </div>
    </div>

    <div x-data="{ preview: null }">
      <label class="mb-1 block text-sm font-medium">Upload New Logo</label>
      <input type="file" name="logo" accept="image/*"
             @change="preview = $event.target.files[0] ? URL.createObjectURL($event.target.files[0]) : null"
             class="w-full rounded border p-2 file:mr-3 file:rounded file:border-0 file:bg-[color:var(--accent)] file:px-3 file:py-2 file:text-white dark:bg-gray-900 dark:text-white">
      @error('logo')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
      <template x-if="preview">
        <img :src="preview" class="mt-3 h-24 w-24 rounded-full object-cover ring-1 ring-black/5 dark:ring-white/10">
      </template>
    </div>

    <div class="grid grid-cols-2 gap-3">
      <div>
        <label class="mb-1 block text-sm font-medium">Order</label>
        <input type="number" name="order" value="{{ old('order',$partner->order) }}" class="w-full rounded border p-2 dark:bg-gray-900 dark:text-white">
      </div>
      <div>
        <label class="mb-1 block text-sm font-medium">Status</label>
        <select name="status" class="w-full rounded border p-2 dark:bg-gray-900 dark:text-white">
          <option value="draft" @selected(old('status',$partner->status)==='draft')>Draft</option>
          <option value="published" @selected(old('status',$partner->status)==='published')>Published</option>
        </select>
      </div>
    </div>

    <div class="mt-2">
      <button class="rounded bg-[color:var(--accent)] px-4 py-2 text-white">Update</button>
      <a href="{{ route('admin.partners.index') }}" class="ml-3 text-gray-600 hover:underline">Cancel</a>
    </div>
  </form>
</x-admin-layout>
