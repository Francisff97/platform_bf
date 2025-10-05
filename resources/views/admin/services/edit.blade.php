<x-admin-layout title="Edit service">
  <x-slot name="header">
    <h1 class="text-xl font-bold">Edit Service</h1>
  </x-slot>

  <form method="POST" action="{{ route('admin.services.update',$service) }}" class="grid gap-4 max-w-2xl" enctype="multipart/form-data">
    @csrf @method('PUT')
    <input type="file" name="image" accept="image/*" class="border p-2 rounded">
  @if(!empty($pack->image_path))
    <img src="{{ asset('storage/'.$service->image_path) }}" class="h-24 mt-2 rounded"/>
  @endif
    <input name="name" class="border p-2 rounded" value="{{ old('name',$service->name) }}">
    <input name="slug" class="border p-2 rounded" value="{{ old('slug',$service->slug) }}">
    <input name="excerpt" class="border p-2 rounded" value="{{ old('excerpt',$service->excerpt) }}">
    <textarea name="body" class="border p-2 rounded" rows="6">{{ old('body',$service->body) }}</textarea>
    <div class="grid grid-cols-2 gap-3">
      <input name="order" type="number" class="border p-2 rounded" value="{{ old('order',$service->order) }}">
      <select name="status" class="border p-2 rounded">
        <option value="draft" {{ $service->status==='draft'?'selected':'' }}>Draft</option>
        <option value="published" {{ $service->status==='published'?'selected':'' }}>Published</option>
      </select>
    </div>
    <div>
      <button class="rounded bg-indigo-600 px-4 py-2 text-white hover:bg-indigo-500">Update</button>
      <a href="{{ route('admin.services.index') }}" class="ml-3 text-gray-600 hover:underline">Cancel</a>
    </div>
  </form>
</x-admin-layout>
