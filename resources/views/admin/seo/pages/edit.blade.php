<x-admin-layout title="Edit SEO Page">
  <form method="POST" action="{{ route('admin.seo.pages.update',$seoPage) }}" class="grid gap-4 max-w-3xl" enctype="multipart/form-data">
    @csrf @method('PUT')

    <label class="block">
      <span class="text-sm">Route name</span>
      <select name="route_name" class="mt-1 w-full rounded border p-2 dark:bg-gray-900 dark:border-gray-800">
        <option value="">—</option>
        @foreach($publicRoutes as $r)
          <option value="{{ $r }}" @selected($seoPage->route_name===$r)>{{ $r }}</option>
        @endforeach
      </select>
    </label>

    <label class="block">
      <span class="text-sm">Path</span>
      <input name="path" value="{{ $seoPage->path }}" class="mt-1 w-full rounded border p-2 dark:bg-gray-900 dark:border-gray-800">
    </label>

    <label class="block">
      <span class="text-sm">Meta title</span>
      <input name="meta_title" value="{{ $seoPage->meta_title }}" class="mt-1 w-full rounded border p-2 dark:bg-gray-900 dark:border-gray-800">
    </label>

    <label class="block">
      <span class="text-sm">Meta description</span>
      <textarea name="meta_description" rows="3" class="mt-1 w-full rounded border p-2 dark:bg-gray-900 dark:border-gray-800">{{ $seoPage->meta_description }}</textarea>
    </label>

    <label class="block">
      <span class="text-sm">OpenGraph image</span>
      <input type="file" name="og_image" accept="image/*" class="mt-1 w-full rounded border p-2 dark:bg-gray-900 dark:border-gray-800">
      @if($seoPage->og_image_path)
        <img src="{{ Storage::url($seoPage->og_image_path) }}" class="h-20 mt-2 rounded">
      @endif
    </label>

    <div class="mt-2">
      <button class="rounded bg-[var(--accent)] px-4 py-2 text-white">Update</button>
    </div>
  </form>
</x-admin-layout>
