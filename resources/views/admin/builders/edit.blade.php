<x-admin-layout title="Edit Builder">
  <x-slot name="header"><h1 class="text-xl font-bold">Edit Builder</h1></x-slot>

  @if ($errors->any())
    <div class="mb-4 rounded border border-red-300 bg-red-50 p-3 text-sm text-red-700">
      <div class="font-semibold mb-2">Correct this errore:</div>
      <ul class="list-disc pl-5">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
  @endif

  <form method="POST" action="{{ route('admin.builders.update',$builder) }}" enctype="multipart/form-data" class="grid gap-4 max-w-2xl">
    @csrf @method('PUT')
    <input name="name" class="border p-2 rounded" value="{{ old('name',$builder->name) }}">
    <input name="slug" class="border p-2 rounded" value="{{ old('slug',$builder->slug) }}">
    <input name="team" class="border p-2 rounded" value="{{ old('team',$builder->team) }}">

    <label class="text-sm text-gray-600 dark:text-gray-300">Skills (separate with comma or enter)</label>
    <textarea name="skills" class="border p-2 rounded" rows="3">{{ old('skills', is_array($builder->skills) ? implode(', ', $builder->skills) : '') }}</textarea>

    <label class="block text-sm mb-1">Description</label>
<textarea name="description" rows="6" class="w-full rounded border p-2"
          placeholder="Bio / description of builder…">{{ old('description', $builder->description ?? '') }}</textarea>


    <label class="text-sm text-gray-600 dark:text-gray-300">Image</label>
    <input type="file" name="image" accept="image/*" class="border p-2 rounded">
    @if($builder->image_path)
      <img src="{{ asset('storage/'.$builder->image_path) }}" class="h-24 mt-2 rounded" alt="">
    @endif

    <div>
      <button class="rounded bg-indigo-600 px-4 py-2 text-white hover:bg-indigo-500">Update</button>
      <a href="{{ route('admin.builders.index') }}" class="ml-3 text-gray-600 hover:underline">Cancel</a>
    </div>
  </form>
</x-admin-layout>
