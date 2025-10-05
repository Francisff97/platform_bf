<x-admin-layout title="About – {{ $section->exists ? 'Edit' : 'Create' }}">
  <form class="grid max-w-3xl gap-4" method="POST"
        action="{{ $section->exists ? route('about-sections.update',$section) : route('about-sections.store') }}"
        enctype="multipart/form-data">
    @csrf
    @if($section->exists) @method('PUT') @endif

    <div class="grid gap-3 md:grid-cols-2">
      <label class="block">
        <div class="text-sm mb-1">Layout</div>
        <select name="layout" class="w-full rounded border px-3 py-2 dark:bg-gray-900">
          @foreach(['text'=>'Text','image_left'=>'Image left','image_right'=>'Image right','hero'=>'Hero'] as $k=>$lbl)
            <option value="{{ $k }}" @selected(old('layout',$section->layout)===$k)>{{ $lbl }}</option>
          @endforeach
        </select>
      </label>

      <label class="block">
        <div class="text-sm mb-1">Position</div>
        <input type="number" name="position" value="{{ old('position',$section->position) }}" class="w-full rounded border px-3 py-2 dark:bg-gray-900">
      </label>
    </div>

    <label class="block">
      <div class="text-sm mb-1">Title</div>
      <input name="title" value="{{ old('title',$section->title) }}" class="w-full rounded border px-3 py-2 dark:bg-gray-900">
    </label>

    <label class="block">
      <div class="text-sm mb-1">Body</div>
      <textarea name="body" rows="6" class="w-full rounded border px-3 py-2 dark:bg-gray-900">{{ old('body',$section->body) }}</textarea>
    </label>

    <div class="grid gap-3 md:grid-cols-2 items-end">
      <label class="block">
        <div class="text-sm mb-1">Image</div>
        <input type="file" name="image" accept="image/*" class="w-full rounded border px-3 py-2">
      </label>
      @if($section->image_path)
        <img src="{{ Storage::url($section->image_path) }}" class="h-20 rounded border dark:border-gray-700" alt="">
      @endif
    </div>

    <div class="flex gap-6">
      <label class="inline-flex items-center gap-2">
        <input type="checkbox" name="featured" value="1" @checked(old('featured',$section->featured))>
        <span>Show in homepage (featured)</span>
      </label>
      <label class="inline-flex items-center gap-2">
        <input type="checkbox" name="is_active" value="1" @checked(old('is_active',$section->is_active ?? true))>
        <span>Active</span>
      </label>
    </div>

    <div class="pt-2">
      <button class="rounded bg-[var(--accent)] px-4 py-2 text-white">{{ $section->exists ? 'Update' : 'Create' }}</button>
      <a href="{{ route('about-sections.index') }}" class="ml-3 underline">Cancel</a>
    </div>
  </form>
</x-admin-layout>