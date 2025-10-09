<x-admin-layout title="Edit Pack">
  <x-slot name="header">
    <h1 class="text-xl font-bold">Edit Pack</h1>
  </x-slot>

  <form method="POST"
        action="{{ route('admin.packs.update',$pack) }}"
        enctype="multipart/form-data"
        class="mx-auto grid w-full max-w-3xl gap-5 rounded-2xl border border-[color:var(--accent)]/30 bg-white/70 p-6 shadow-sm backdrop-blur
               dark:border-[color:var(--accent)]/30 dark:bg-gray-900/70">
    @csrf @method('PUT')

    @if ($errors->any())
      <div class="rounded border border-red-300 bg-red-50/80 p-3 text-sm text-red-700 dark:border-red-500 dark:bg-red-900/40 dark:text-red-200">
        <div class="font-semibold mb-2">Fix these errors:</div>
        <ul class="list-disc pl-5 space-y-0.5">
          @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    <div>
      <label class="block text-sm font-medium text-gray-800 dark:text-gray-200">Image</label>
      <input type="file" name="image" accept="image/*"
             class="w-full rounded-xl border border-[color:var(--accent)] bg-white/90 p-2 text-sm text-black outline-none transition
                    file:mr-3 file:rounded-lg file:border-0 file:bg-[color:var(--accent)] file:px-3 file:py-2 file:text-white
                    hover:border-[color:var(--accent)]/80 focus:ring-2 focus:ring-[color:var(--accent)]
                    dark:bg-black/80 dark:text-white" />
      @if(!empty($pack->image_path))
        <img src="{{ asset('storage/'.$pack->image_path) }}" class="h-24 mt-3 rounded-lg ring-1 ring-black/5 dark:ring-white/10"/>
      @endif
    </div>

    <x-input label="Title" name="title" value="{{ old('title',$pack->title) }}" />
    <x-input label="Slug" name="slug" value="{{ old('slug',$pack->slug) }}" />
    <x-input label="Excerpt" name="excerpt" value="{{ old('excerpt',$pack->excerpt) }}" />
    <x-textarea label="Description" name="description" rows="6">{{ old('description',$pack->description) }}</x-textarea>

    <div class="grid grid-cols-2 gap-4">
      <x-input label="Price (in cents)" name="price_cents" type="number" value="{{ old('price_cents',$pack->price_cents) }}" />
      <x-input label="Currency" name="currency" value="{{ old('currency',$pack->currency) }}" />
    </div>

    <div class="grid grid-cols-2 gap-4">
      <div>
        <label class="mb-1 block text-sm font-medium text-gray-800 dark:text-gray-200">Category</label>
        <select name="category_id"
                class="h-11 w-full rounded-xl border border-[color:var(--accent)] bg-white/90 px-3 text-black outline-none transition
                       hover:border-[color:var(--accent)]/80 focus:ring-2 focus:ring-[color:var(--accent)]
                       dark:bg-black/80 dark:text-white">
          <option value="">— Select a category —</option>
          @foreach($categories as $c)
            <option value="{{ $c->id }}" @selected(old('category_id',$pack->category_id)==$c->id)>{{ $c->name }}</option>
          @endforeach
        </select>
      </div>

      <div>
        <label class="mb-1 block text-sm font-medium text-gray-800 dark:text-gray-200">Builder</label>
        <select name="builder_id"
                class="h-11 w-full rounded-xl border border-[color:var(--accent)] bg-white/90 px-3 text-black outline-none transition
                       hover:border-[color:var(--accent)]/80 focus:ring-2 focus:ring-[color:var(--accent)]
                       dark:bg-black/80 dark:text-white">
          <option value="">— Select a builder —</option>
          @foreach($builders as $b)
            <option value="{{ $b->id }}" @selected(old('builder_id',$pack->builder_id)==$b->id)>{{ $b->name }}</option>
          @endforeach
        </select>
      </div>
    </div>

    <div class="grid grid-cols-2 gap-4">
      <select name="status"
              class="h-11 w-full rounded-xl border border-[color:var(--accent)] bg-white/90 px-3 text-black outline-none transition
                     hover:border-[color:var(--accent)]/80 focus:ring-2 focus:ring-[color:var(--accent)]
                     dark:bg-black/80 dark:text-white">
        <option value="draft" {{ $pack->status==='draft'?'selected':'' }}>Draft</option>
        <option value="published" {{ $pack->status==='published'?'selected':'' }}>Published</option>
      </select>

      <x-input label="Published at" name="published_at" type="datetime-local"
               value="{{ old('published_at', optional($pack->published_at)->format('Y-m-d\TH:i')) }}" />
    </div>

    <label class="inline-flex items-center gap-2 text-sm text-gray-800 dark:text-gray-200">
      <input type="checkbox" name="is_featured" value="1" {{ old('is_featured',$pack->is_featured)?'checked':'' }}
             class="rounded border-gray-300 dark:border-gray-600">
      Featured
    </label>

    <div class="mt-2 flex items-center gap-3">
      <button class="inline-flex items-center justify-center rounded-xl bg-[color:var(--accent)] px-5 py-2.5 text-white transition hover:opacity-90">
        Update
      </button>
      <a href="{{ route('admin.packs.index') }}" class="text-gray-600 hover:underline dark:text-gray-300">Cancel</a>
    </div>
  </form>
</x-admin-layout>