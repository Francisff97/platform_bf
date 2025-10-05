{{-- resources/views/admin/packs/create.blade.php --}}
<x-admin-layout title="New Pack">
  <div class="mb-4 flex items-center justify-between">
    <h2 class="text-lg font-semibold">Create Pack</h2>
    <a href="{{ route('admin.packs.index') }}" class="text-sm underline">Back to list</a>
  </div>

  @if ($errors->any())
    <div class="mb-4 rounded border border-red-300 bg-red-50 p-3 text-sm text-red-700">
      <div class="mb-2 font-semibold">Please fix these errors:</div>
      <ul class="list-disc pl-5">
        @foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
      </ul>
    </div>
  @endif

  <form method="POST" action="{{ route('admin.packs.store') }}" enctype="multipart/form-data" class="grid max-w-3xl gap-4">
    @csrf

    <div class="grid gap-3 md:grid-cols-2">
      <div>
        <label class="block text-sm text-gray-600">Title</label>
        <input name="title" class="mt-1 w-full rounded border p-2" value="{{ old('title') }}" required>
      </div>
      <div>
        <label class="block text-sm text-gray-600">Slug (optional)</label>
        <input name="slug" class="mt-1 w-full rounded border p-2" value="{{ old('slug') }}">
      </div>
    </div>

    <div>
      <label class="block text-sm text-gray-600">Excerpt</label>
      <input name="excerpt" class="mt-1 w-full rounded border p-2" value="{{ old('excerpt') }}">
    </div>

    <div>
      <label class="block text-sm text-gray-600">Description</label>
      <textarea name="description" rows="6" class="mt-1 w-full rounded border p-2">{{ old('description') }}</textarea>
    </div>

    <div>
      <label class="block text-sm text-gray-600">Image</label>
      <input type="file" name="image" accept="image/*" class="mt-1 w-full rounded border p-2">
    </div>

    <div class="grid gap-3 md:grid-cols-2">
      <div>
        <label class="block text-sm text-gray-600">Price (in cents)</label>
        <input type="number" name="price_cents" class="mt-1 w-full rounded border p-2" value="{{ old('price_cents', 0) }}" min="0" required>
      </div>
      <div>
        <label class="block text-sm text-gray-600">Currency</label>
        <input
          name="currency"
          class="mt-1 w-full rounded border p-2 uppercase"
          value="{{ old('currency', $siteCurrency ?? 'EUR') }}"
          maxlength="3"
          required
        >
        <p class="mt-1 text-xs text-gray-500">
          Default: {{ strtoupper($siteCurrency ?? 'EUR') }}
        </p>
      </div>
    </div>

    <div class="grid gap-3 md:grid-cols-2">
      <div>
        <label class="block text-sm text-gray-600">Category</label>
        <select name="category_id" class="mt-1 w-full rounded border p-2">
          <option value="">— None —</option>
          @foreach($categories as $c)
            <option value="{{ $c->id }}" @selected(old('category_id')==$c->id)>{{ $c->name }}</option>
          @endforeach
        </select>
      </div>
      <div>
        <label class="block text-sm text-gray-600">Builder</label>
        <select name="builder_id" class="mt-1 w-full rounded border p-2">
          <option value="">— None —</option>
          @foreach($builders as $b)
            <option value="{{ $b->id }}" @selected(old('builder_id')==$b->id)>{{ $b->name }}</option>
          @endforeach
        </select>
      </div>
    </div>

    <div class="grid gap-3 md:grid-cols-2">
      <div>
        <label class="block text-sm text-gray-600">Status</label>
        <select name="status" class="mt-1 w-full rounded border p-2" required>
          <option value="draft" @selected(old('status')==='draft')>Draft</option>
          <option value="published" @selected(old('status')==='published')>Published</option>
        </select>
      </div>
      <div>
        <label class="block text-sm text-gray-600">Published at</label>
        <input type="datetime-local" name="published_at" class="mt-1 w-full rounded border p-2" value="{{ old('published_at') }}">
      </div>
    </div>

    <label class="inline-flex items-center gap-2">
      <input type="checkbox" name="is_featured" value="1" @checked(old('is_featured'))>
      <span class="text-sm">Featured</span>
    </label>

    <div class="pt-2">
      <button class="rounded bg-[var(--accent)] px-4 py-2 text-white hover:opacity-90">Save</button>
    </div>
  </form>
</x-admin-layout>