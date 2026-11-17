<x-admin-layout title="Create SEO Page">
  <form method="POST" action="{{ route('admin.seo.pages.store') }}" class="grid gap-4 max-w-3xl" enctype="multipart/form-data">
    @csrf
    <label class="block">
      <span class="text-sm">Route name (opzionale)</span>
      <select name="route_name" class="mt-1 w-full rounded border p-2 dark:bg-gray-900 dark:border-gray-800">
        <option value="">—</option>
        @foreach($publicRoutes as $r)
          <option value="{{ $r }}">{{ $r }}</option>
        @endforeach
      </select>
    </label>

    <label class="block">
      <span class="text-sm">Path (fallback, es. /contacts)</span>
      <input name="path" class="mt-1 w-full rounded border p-2 dark:bg-gray-900 dark:border-gray-800" placeholder="/contacts">
    </label>

    <label class="block">
      <span class="text-sm">Meta title</span>
      <input name="meta_title" class="mt-1 w-full rounded border p-2 dark:bg-gray-900 dark:border-gray-800">
    </label>

    <label class="block">
      <span class="text-sm">Meta description</span>
      <textarea name="meta_description" rows="3" class="mt-1 w-full rounded border p-2 dark:bg-gray-900 dark:border-gray-800"></textarea>
    </label>

    <label class="block">
      <span class="text-sm">OpenGraph image</span>
      <input type="file" name="og_image" accept="image/*" class="mt-1 w-full rounded border p-2 dark:bg-gray-900 dark:border-gray-800">
    </label>

    <div class="mt-2">
      <button class="rounded bg-[var(--accent)] px-4 py-2 text-white">Save</button>
    </div>
  </form>
</x-admin-layout>
