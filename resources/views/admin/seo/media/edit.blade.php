<x-admin-layout title="Edit Media">
  <div class="grid gap-4 max-w-2xl">
    <img src="{{ $mediaAsset->url() }}" class="h-32 w-32 object-cover rounded">
    <div class="text-xs text-gray-500">{{ $mediaAsset->path }}</div>

    <form method="POST" action="{{ route('admin.seo.media.update',$mediaAsset) }}" class="grid gap-3">
      @csrf @method('PUT')
      <label class="block">
        <span class="text-sm">Alt text</span>
        <input name="alt_text" value="{{ old('alt_text',$mediaAsset->alt_text) }}" class="mt-1 w-full rounded border p-2 dark:bg-gray-900 dark:border-gray-800">
      </label>
      <label class="inline-flex items-center gap-2 text-sm">
        <input type="checkbox" name="is_lazy" value="1" @checked($mediaAsset->is_lazy)> Lazy loading
      </label>
      <div>
        <button class="rounded bg-[var(--accent)] px-4 py-2 text-white">Save</button>
      </div>
    </form>
  </div>
</x-admin-layout>
