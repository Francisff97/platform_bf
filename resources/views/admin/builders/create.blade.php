<x-admin-layout title="Create Builder">
  <x-slot name="header"><h1 class="text-xl font-bold">New Builder</h1></x-slot>

  @if ($errors->any())
    <div class="mb-4 rounded border border-red-300 bg-red-50 p-3 text-sm text-red-700">
      <div class="font-semibold mb-2">Correct thats error:</div>
      <ul class="list-disc pl-5">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
  @endif

  <form method="POST" action="{{ route('admin.builders.store') }}" enctype="multipart/form-data" class="grid gap-4 max-w-2xl">
    @csrf
    <input name="name" class="border p-2 rounded" placeholder="Name" value="{{ old('name') }}">
    <input name="slug" class="border p-2 rounded" placeholder="Slug (optional)" value="{{ old('slug') }}">
    <input name="team" class="border p-2 rounded" placeholder="Team" value="{{ old('team') }}">

    <label class="text-sm text-gray-600 dark:text-gray-300">Skills (separate with comma or enter)</label>
    <textarea name="skills" class="border p-2 rounded" rows="3" placeholder="e.g. TH16, Anti-3, Hybrid">{{ old('skills') }}</textarea>

    <label class="block text-sm mb-1">Description</label>
<textarea name="description" rows="6" class="w-full rounded border p-2"
          placeholder="Bio / description of the builder…">{{ old('description', $builder->description ?? '') }}</textarea>

    <label class="text-sm text-gray-600 dark:text-gray-300">Image</label>
    <input type="file" name="image" accept="image/*" class="border p-2 rounded">

    <div>
      <button class="rounded bg-indigo-600 px-4 py-2 text-white hover:bg-indigo-500">Save</button>
      <a href="{{ route('admin.builders.index') }}" class="ml-3 text-gray-600 hover:underline">Cancel</a>
    </div>
  </form>
</x-admin-layout>
