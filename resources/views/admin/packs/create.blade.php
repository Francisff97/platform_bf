<x-admin-layout title="New Pack">
  <div class="mb-4 flex items-center justify-between">
    <h2 class="text-lg font-semibold">Create Pack</h2>
    <a href="{{ route('admin.packs.index') }}" class="text-sm underline">Back to list</a>
  </div>

  @if ($errors->any())
    <div class="mb-4 rounded border border-red-300 bg-red-50/80 p-3 text-sm text-red-700 dark:border-red-500 dark:bg-red-900/40 dark:text-red-200">
      <div class="mb-2 font-semibold">Please fix these errors:</div>
      <ul class="list-disc pl-5 space-y-0.5">
        @foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
      </ul>
    </div>
  @endif

  <form method="POST"
        action="{{ route('admin.packs.store') }}"
        enctype="multipart/form-data"
        class="mx-auto grid w-full max-w-3xl gap-5 rounded-2xl border border-[color:var(--accent)]/30 bg-white/70 p-6 shadow-sm backdrop-blur
               dark:border-[color:var(--accent)]/30 dark:bg-gray-900/70">
    @csrf

    <div class="grid gap-3 md:grid-cols-2">
      <x-input label="Title" name="title" value="{{ old('title') }}" required />
      <x-input label="Slug (optional)" name="slug" value="{{ old('slug') }}" />
    </div>

    <x-input label="Excerpt" name="excerpt" value="{{ old('excerpt') }}" />
    <x-textarea label="Description" name="description" rows="6">{{ old('description') }}</x-textarea>

    {{-- Image --}}
    <div>
      <label class="mb-1 block text-sm font-medium text-gray-800 dark:text-gray-200">Image</label>
      <input type="file" name="image" accept="image/*"
             class="w-full rounded-xl border border-[color:var(--accent)] bg-white/90 p-2 text-sm text-black outline-none transition
                    file:mr-3 file:rounded-lg file:border-0 file:bg-[color:var(--accent)] file:px-3 file:py-2 file:text-white
                    hover:border-[color:var(--accent)]/80 focus:ring-2 focus:ring-[color:var(--accent)]
                    dark:bg-black/80 dark:text-white" />
    </div>
    <x-admin.image-hint :model="$pack ?? \App\Models\Pack::class" field="image_path"/>
    <div class="grid gap-3 md:grid-cols-2">
      <x-input label="Price (in cents)" name="price_cents" type="number" value="{{ old('price_cents',0) }}" min="0" required />
      <div>
        <x-input label="Currency" name="currency" class="uppercase" value="{{ old('currency', $siteCurrency ?? 'EUR') }}" maxlength="3" required />
        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
          Default: {{ strtoupper($siteCurrency ?? 'EUR') }}
        </p>
      </div>
    </div>

    <div class="grid gap-3 md:grid-cols-2">
      <div>
        <label class="mb-1 block text-sm font-medium text-gray-800 dark:text-gray-200">Category</label>
        <select name="category_id"
                class="w-full rounded-xl border border-[color:var(--accent)] bg-white/90 px-3 py-2 text-black outline-none transition
                       hover:border-[color:var(--accent)]/80 focus:ring-2 focus:ring-[color:var(--accent)]
                       dark:bg-black/80 dark:text-white">
          <option value="">— None —</option>
          @foreach($categories as $c)
            <option value="{{ $c->id }}" @selected(old('category_id')==$c->id)>{{ $c->name }}</option>
          @endforeach
        </select>
      </div>
      <div>
        <label class="mb-1 block text-sm font-medium text-gray-800 dark:text-gray-200">Builder</label>
        <select name="builder_id"
                class="w-full rounded-xl border border-[color:var(--accent)] bg-white/90 px-3 py-2 text-black outline-none transition
                       hover:border-[color:var(--accent)]/80 focus:ring-2 focus:ring-[color:var(--accent)]
                       dark:bg-black/80 dark:text-white">
          <option value="">— None —</option>
          @foreach($builders as $b)
            <option value="{{ $b->id }}" @selected(old('builder_id')==$b->id)>{{ $b->name }}</option>
          @endforeach
        </select>
      </div>
    </div>

    <div class="grid gap-3 md:grid-cols-2">
      <div>
        <label class="mb-1 block text-sm font-medium text-gray-800 dark:text-gray-200">Status</label>
        <select name="status"
                class="h-11 w-full rounded-xl border border-[color:var(--accent)] bg-white/90 px-3 text-black outline-none transition
                       hover:border-[color:var(--accent)]/80 focus:ring-2 focus:ring-[color:var(--accent)]
                       dark:bg-black/80 dark:text-white">
          <option value="draft" @selected(old('status')==='draft')>Draft</option>
          <option value="published" @selected(old('status')==='published')>Published</option>
        </select>
      </div>
      <x-input label="Published at" name="published_at" type="datetime-local" value="{{ old('published_at') }}" />
    </div>

    <label class="inline-flex items-center gap-2 text-sm text-gray-800 dark:text-gray-200">
      <input type="checkbox" name="is_featured" value="1" @checked(old('is_featured')) class="rounded border-gray-300 dark:border-gray-600">
      Featured
    </label>

    <div class="pt-2">
      <button class="inline-flex items-center justify-center rounded-xl bg-[color:var(--accent)] px-5 py-2.5 text-white transition hover:opacity-90 active:opacity-80">
        Save
      </button>
    </div>
  </form>
</x-admin-layout>