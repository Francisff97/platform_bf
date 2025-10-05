<x-admin-layout title="Create Service">
  <x-slot name="header">
    <h1 class="text-xl font-bold">New Service</h1>
  </x-slot>

  <form method="POST" action="{{ route('admin.services.store') }}" class="grid gap-4 max-w-2xl" enctype="multipart/form-data">
    @csrf
    <input type="file" name="image" accept="image/*" class="border p-2 rounded">
  @if(!empty($pack->image_path))
    <img src="{{ asset('storage/'.$service->image_path) }}" class="h-24 mt-2 rounded"/>
  @endif
    <input name="name" class="border p-2 rounded" placeholder="Name" value="{{ old('name') }}">
    <input name="slug" class="border p-2 rounded" placeholder="Slug (optional)" value="{{ old('slug') }}">
    <input name="excerpt" class="border p-2 rounded" placeholder="Excerpt" value="{{ old('excerpt') }}">
    <textarea name="body" class="border p-2 rounded" rows="6" placeholder="Description">{{ old('body') }}</textarea>
    <div class="grid grid-cols-2 gap-3">
      <input name="order" type="number" class="border p-2 rounded" value="{{ old('order',0) }}">
      <select name="status" class="border p-2 rounded">
        <option value="draft">Draft</option>
        <option value="published" selected>Pubblished</option>
      </select>
    </div>
    <div>
      <button class="rounded bg-indigo-600 px-4 py-2 text-white hover:bg-indigo-500">Save</button>
      <a href="{{ route('admin.services.index') }}" class="ml-3 text-gray-600 hover:underline">Cancel</a>
    </div>
  </form>
</x-admin-layout>
