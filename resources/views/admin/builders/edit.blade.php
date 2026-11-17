<x-admin-layout title="Edit Builder">
  <x-slot name="header"><h1 class="text-xl font-bold">Edit Builder</h1></x-slot>

  @if ($errors->any())
    <div class="mb-4 rounded-xl border border-red-300 bg-red-50/80 px-4 py-3 text-sm text-red-700 dark:border-red-500/40 dark:bg-red-900/20 dark:text-red-200">
      <div class="mb-2 font-semibold">Correct these errors:</div>
      <ul class="list-disc pl-5">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
  @endif

  <form method="POST" action="{{ route('admin.builders.update',$builder) }}" enctype="multipart/form-data"
        class="mx-auto grid w-full max-w-2xl gap-5 rounded-2xl border border-[color:var(--accent)]/30 bg-white/70 p-6 shadow-sm backdrop-blur
               dark:border-[color:var(--accent)]/30 dark:bg-gray-900/70">
    @csrf @method('PUT')

    <div>
      <label class="mb-1 block text-sm font-medium text-gray-800 dark:text-gray-200">Name</label>
      <input name="name" value="{{ old('name',$builder->name) }}"
             class="h-11 w-full rounded-xl border border-[color:var(--accent)] bg-white/90 px-3 text-black outline-none transition
                    hover:border-[color:var(--accent)]/80 focus:ring-2 focus:ring-[color:var(--accent)]
                    dark:bg-black/80 dark:text-white" />
    </div>

    <div>
      <label class="mb-1 block text-sm font-medium text-gray-800 dark:text-gray-200">Slug</label>
      <input name="slug" value="{{ old('slug',$builder->slug) }}"
             class="h-11 w-full rounded-xl border border-[color:var(--accent)] bg-white/90 px-3 text-black outline-none transition
                    hover:border-[color:var(--accent)]/80 focus:ring-2 focus:ring-[color:var(--accent)]
                    dark:bg-black/80 dark:text-white" />
    </div>

    <div>
      <label class="mb-1 block text-sm font-medium text-gray-800 dark:text-gray-200">Team</label>
      <input name="team" value="{{ old('team',$builder->team) }}"
             class="h-11 w-full rounded-xl border border-[color:var(--accent)] bg-white/90 px-3 text-black outline-none transition
                    hover:border-[color:var(--accent)]/80 focus:ring-2 focus:ring-[color:var(--accent)]
                    dark:bg-black/80 dark:text-white" />
    </div>

    <div>
      <label class="mb-1 block text-sm font-medium text-gray-800 dark:text-gray-200">Skills</label>
      <textarea name="skills" rows="3" placeholder="Comma separated"
                class="w-full rounded-xl border border-[color:var(--accent)] bg-white/90 p-3 text-black outline-none transition
                       hover:border-[color:var(--accent)]/80 focus:ring-2 focus:ring-[color:var(--accent)]
                       dark:bg-black/80 dark:text-white">{{ old('skills', is_array($builder->skills) ? implode(', ', $builder->skills) : '') }}</textarea>
    </div>

    <div>
      <label class="mb-1 block text-sm font-medium text-gray-800 dark:text-gray-200">Description</label>
      <textarea name="description" rows="6" placeholder="Bio / description of builder…"
                class="w-full rounded-xl border border-[color:var(--accent)] bg-white/90 p-3 text-black outline-none transition
                       hover:border-[color:var(--accent)]/80 focus:ring-2 focus:ring-[color:var(--accent)]
                       dark:bg-black/80 dark:text-white">{{ old('description', $builder->description ?? '') }}</textarea>
    </div>

    {{-- Image + current + preview --}}
    <div x-data="{preview:null}">
      <label class="mb-1 block text-sm font-medium text-gray-800 dark:text-gray-200">Image</label>
      <input type="file" name="image" accept="image/*"
             @change="preview = $event.target.files?.[0] ? URL.createObjectURL($event.target.files[0]) : null"
             class="w-full rounded-xl border border-[color:var(--accent)] bg-white/90 p-2 text-sm text-black outline-none transition
                    file:mr-3 file:rounded-lg file:border-0 file:bg-[color:var(--accent)] file:px-3 file:py-2 file:text-white
                    hover:border-[color:var(--accent)]/80 focus:ring-2 focus:ring-[color:var(--accent)]
                    dark:bg-black/80 dark:text-white" />
      @if($builder->image_path)
        <img src="{{ asset('storage/'.$builder->image_path) }}" class="mt-3 h-24 rounded-lg object-cover ring-1 ring-black/5 dark:ring-white/10" alt="">
      @endif
      <template x-if="preview">
        <img :src="preview" class="mt-3 h-24 rounded-lg object-cover ring-1 ring-black/5 dark:ring-white/10" alt="Preview">
      </template>
    </div>
    <x-admin.image-hint field="avatar"/>
    <div class="mt-1 flex items-center gap-3">
      <button class="rounded-xl bg-[var(--accent)] px-5 py-2.5 text-white hover:opacity-90">Update</button>
      <a href="{{ route('admin.builders.index') }}" class="text-gray-600 hover:underline dark:text-gray-300">Cancel</a>
    </div>
  </form>
</x-admin-layout>