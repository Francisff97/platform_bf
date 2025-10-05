<x-admin-layout title="Edit Category">
  <form method="POST" action="{{ route('admin.categories.update',$category) }}" class="grid max-w-xl gap-4">
    @csrf @method('PUT')
    <div>
      <label class="block text-sm text-gray-600 dark:text-white">Name</label>
      <input name="name" class="mt-1 w-full rounded border p-2 dark:text-black" required value="{{ old('name',$category->name) }}">
    </div>
    <div>
      <label class="block text-sm text-gray-600 dark:text-white">Slug</label>
      <input name="slug" class="mt-1 w-full rounded border p-2 dark:text-black" value="{{ old('slug',$category->slug) }}">
    </div>
    <div>
      <label class="block text-sm font-medium dark:text-white">Color</label>
<input type="text" name="color" value="{{ old('color', $category->color ?? '') }}"
       class="mt-1 w-full rounded border px-3 py-2 dark:text-black"
       placeholder="indigo  |  #4f46e5">
<p class="mt-1 text-xs text-gray-500">
  Use a palette name (indigo, emerald, …) or a HEX like <code>#4f46e5</code>.
</p>

    <div class="pt-2">
      <button class="rounded bg-[var(--accent)] px-4 py-2 text-white hover:opacity-90">Update</button>
    </div>
  </form>
</x-admin-layout>