<x-admin-layout title="Edit Pack">
  <x-slot name="header">
    <h1 class="text-xl font-bold">Edit Pack</h1>
  </x-slot>

  <form method="POST" action="{{ route('admin.packs.update',$pack) }}" enctype="multipart/form-data" class="grid gap-4 max-w-2xl">
    @csrf @method('PUT')
    <input type="file" name="image" accept="image/*" class="border p-2 rounded">
  @if(!empty($pack->image_path))
    <img src="{{ asset('storage/'.$pack->image_path) }}" class="h-24 mt-2 rounded"/>
  @endif
  <select name="category_id" class="border p-2 rounded">
  <option value="">— Select a category —</option>
  @foreach($categories as $c)
    <option value="{{ $c->id }}" @selected(old('category_id', $pack->category_id ?? '')==$c->id)>{{ $c->name }}</option>
  @endforeach
</select>
@if ($errors->any())
  <div class="rounded border border-red-300 bg-red-50 p-3 text-sm text-red-700">
    <div class="font-semibold mb-2">Fix these errors:</div>
    <ul class="list-disc pl-5">
      @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
      @endforeach
    </ul>
  </div>
@endif

    <input name="title" class="border p-2 rounded" value="{{ old('title',$pack->title) }}">
    <input name="slug" class="border p-2 rounded" value="{{ old('slug',$pack->slug) }}">
    <input name="excerpt" class="border p-2 rounded" value="{{ old('excerpt',$pack->excerpt) }}">
    <textarea name="description" class="border p-2 rounded" rows="6">{{ old('description',$pack->description) }}</textarea>
    <div class="grid grid-cols-2 gap-3">
      <input name="price_cents" type="number" class="border p-2 rounded" value="{{ old('price_cents',$pack->price_cents) }}">
      <input name="currency" class="border p-2 rounded" value="{{ old('currency',$pack->currency) }}">
    </div>
    <label class="inline-flex items-center gap-2">
      <input type="checkbox" name="is_featured" value="1" {{ old('is_featured',$pack->is_featured)?'checked':'' }}>
      <span>Featured</span>
    </label>
    <div class="grid grid-cols-2 gap-3">
      <select name="status" class="border p-2 rounded">
        <option value="draft" {{ $pack->status==='draft'?'selected':'' }}>Draft</option>
        <option value="published" {{ $pack->status==='published'?'selected':'' }}>Published</option>
      </select>
      <select name="builder_id" class="border p-2 rounded">
  <option value="">— Select a builder —</option>
  @foreach($builders as $b)
    <option value="{{ $b->id }}" @selected(old('builder_id', $pack->builder_id ?? '')==$b->id)>{{ $b->name }}</option>
  @endforeach
</select>

      <input name="published_at" type="datetime-local" class="border p-2 rounded" value="{{ old('published_at', optional($pack->published_at)->format('Y-m-d\TH:i')) }}">
    </div>
    <div>
      <button class="rounded bg-indigo-600 px-4 py-2 text-white hover:bg-indigo-500">Update</button>
      <a href="{{ route('admin.packs.index') }}" class="ml-3 text-gray-600 hover:underline">Cancel</a>
    </div>
  </form>
</x-admin-layout>
