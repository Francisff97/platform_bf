<x-admin-layout title="Edit slide">
  @if ($errors->any())
    <div class="mb-4 rounded border border-red-300 bg-red-50 px-3 py-2 text-sm text-red-700">
      <div class="font-semibold mb-1">Fix these errors:</div>
      <ul class="list-disc pl-5">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
  @endif

  <form method="POST" action="{{ route('admin.slides.update',$slide) }}" enctype="multipart/form-data" class="grid max-w-3xl gap-4">
    @csrf @method('PUT')
    <div class="grid md:grid-cols-2 gap-4">
      <div>
        <label class="block text-sm mb-1">Title</label>
        <input name="title" class="w-full rounded border p-2" value="{{ old('title',$slide->title) }}">
      </div>
      <div>
        <label class="block text-sm mb-1">Subtitle</label>
        <input name="subtitle" class="w-full rounded border p-2" value="{{ old('subtitle',$slide->subtitle) }}">
      </div>
    </div>

    <div>
      <label class="block text-sm mb-1">Image</label>
      <input type="file" name="image" accept="image/*" class="w-full rounded border p-2">
      @if($slide->image_path)
        <img src="{{ Storage::url($slide->image_path) }}" class="mt-2 h-24 rounded object-cover" alt="">
      @endif
      <p class="mt-1 text-xs text-gray-500">If you upload a file you replace the actual one.</p>
    </div>

    <div class="grid md:grid-cols-2 gap-4">
      <div>
        <label class="block text-sm mb-1">CTA label</label>
        <input name="cta_label" class="w-full rounded border p-2" value="{{ old('cta_label',$slide->cta_label) }}">
      </div>
      <div>
        <label class="block text-sm mb-1">CTA URL</label>
        <input name="cta_url" class="w-full rounded border p-2" value="{{ old('cta_url',$slide->cta_url) }}">
      </div>
    </div>

    <div class="grid md:grid-cols-2 gap-4">
      <div>
        <label class="block text-sm mb-1">Order</label>
        <input type="number" name="sort_order" class="w-full rounded border p-2" value="{{ old('sort_order',$slide->sort_order) }}">
      </div>
      <div class="flex items-end">
        <label class="inline-flex items-center gap-2">
          <input type="checkbox" name="is_active" value="1" {{ $slide->is_active ? 'checked' : '' }}>
          <span>Active</span>
        </label>
      </div>
    </div>

    <div>
      <button class="rounded bg-[var(--accent)] px-4 py-2 text-white">Update</button>
      <a href="{{ route('admin.slides.index') }}" class="ml-3 text-gray-600 hover:underline">Cancel</a>
    </div>
  </form>
</x-admin-layout>
