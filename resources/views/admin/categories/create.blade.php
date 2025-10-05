<x-admin-layout title="Nuova Categoria">
  <form method="POST" action="{{ route('admin.categories.store') }}" class="grid max-w-xl gap-4">
    @csrf
    <div>
      <label class="block text-sm text-gray-600 dark:text-white">Name</label>
      <input name="name" class="mt-1 w-full rounded border p-2 dark:text-black" required value="{{ old('name') }}">
    </div>
    <div>
      <label class="block text-sm text-gray-600 dark:text-white">Slug (optional)</label>
      <input name="slug" class="mt-1 w-full rounded border p-2 dark:text-black" value="{{ old('slug') }}">
    </div>
    <div>
      <div class="mt-1 grid gap-2">
        <label class="block text-sm font-medium">Color</label>
<input type="text" name="color" value="{{ old('color', $category->color ?? '') }}"
       class="mt-1 w-full rounded border px-3 py-2 dark:text-black"
       placeholder="indigo  |  #4f46e5">
<p class="mt-1 text-xs text-gray-500">
  Use a palette name (indigo, emerald, …) or a HEX like <code>#4f46e5</code>.
</p>

    @if ($errors->any())
      <div class="rounded border border-red-300 bg-red-50 p-3 text-sm text-red-700">
        <div class="font-semibold mb-2">Correct that errors:</div>
        <ul class="list-disc pl-5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
      </div>
    @endif

    <div class="pt-2">
      <button class="rounded bg-[var(--accent)] px-4 py-2 text-white hover:opacity-90">Save</button>
    </div>
  </form>
</x-admin-layout>