<x-admin-layout title="Edit Category">
  <x-slot name="header"><h1 class="text-xl font-bold">Edit Category</h1></x-slot>

  @if ($errors->any())
    <div class="mb-4 rounded-xl border border-red-300 bg-red-50/80 px-4 py-3 text-sm text-red-700
                dark:border-red-500/40 dark:bg-red-900/20 dark:text-red-200">
      <div class="mb-2 font-semibold">Correct these errors:</div>
      <ul class="list-disc pl-5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
  @endif

  <form method="POST" action="{{ route('admin.categories.update',$category) }}"
        class="mx-auto grid w-full max-w-xl gap-5 rounded-2xl border border-[color:var(--accent)]/30 bg-white/70 p-6 shadow-sm backdrop-blur
               dark:border-[color:var(--accent)]/30 dark:bg-gray-900/70">
    @csrf @method('PUT')

    {{-- Name --}}
    <div>
      <label class="mb-1 block text-sm font-medium text-gray-800 dark:text-gray-200">Name</label>
      <input name="name" value="{{ old('name',$category->name) }}" required
             class="h-11 w-full rounded-xl border border-[color:var(--accent)] bg-white/90 px-3 text-black outline-none transition
                    hover:border-[color:var(--accent)]/80 focus:ring-2 focus:ring-[color:var(--accent)]
                    dark:bg-black/80 dark:text-white" />
    </div>

    {{-- Slug --}}
    <div>
      <label class="mb-1 block text-sm font-medium text-gray-800 dark:text-gray-200">Slug</label>
      <input name="slug" value="{{ old('slug',$category->slug) }}"
             class="h-11 w-full rounded-xl border border-[color:var(--accent)] bg-white/90 px-3 text-black outline-none transition
                    hover:border-[color:var(--accent)]/80 focus:ring-2 focus:ring-[color:var(--accent)]
                    dark:bg-black/80 dark:text-white" />
    </div>

    {{-- Color --}}
    <div>
      <label class="mb-1 block text-sm font-medium text-gray-800 dark:text-gray-200">Color</label>
      <input type="text" name="color" value="{{ old('color', $category->color ?? '') }}"
             placeholder="indigo  |  #4f46e5"
             class="h-11 w-full rounded-xl border border-[color:var(--accent)] bg-white/90 px-3 text-black placeholder:text-gray-500 outline-none transition
                    hover:border-[color:var(--accent)]/80 focus:ring-2 focus:ring-[color:var(--accent)]
                    dark:bg-black/80 dark:text-white dark:placeholder:text-gray-400" />
      <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
        Use a palette name (indigo, emerald, …) or a HEX like <code>#4f46e5</code>.
      </p>
    </div>

    <div class="mt-2">
      <button class="inline-flex items-center justify-center rounded-xl bg-[color:var(--accent)] px-5 py-2.5 text-white transition hover:opacity-90">
        Update
      </button>
      <a href="{{ route('admin.categories.index') }}" class="ml-3 text-sm text-gray-600 hover:underline dark:text-gray-300">Cancel</a>
    </div>
  </form>
</x-admin-layout>