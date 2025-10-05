<x-admin-layout title="Sliders">
  <div class="bg-gray-50 p-4 rounded border border-gray-200 dark:bg-gray-800 dark:border-gray-700 text-sm text-gray-600 dark:text-white mb-6">
    Welcome to the Slides management page. Here you can create, edit, and delete slides that appear in the homepage slider. Use the "New Slide" button to add a new slide, and click "Edit" next to an existing slide to modify its content or settings.
  </div>
  <div class="mb-4 flex items-center justify-between">
    <h2 class="text-lg font-semibold">Slides</h2>
    <a href="{{ route('admin.slides.create') }}" class="rounded bg-[var(--accent)] px-3 py-1.5 text-white text-sm">
      Add slide
    </a>
  </div>

  <div class="overflow-hidden rounded border dark:border-gray-800">
    <table class="min-w-full text-sm">
      <thead class="bg-gray-50 dark:bg-gray-900">
        <tr>
          <th class="px-3 py-2 text-left">#</th>
          <th class="px-3 py-2 text-left">Preview</th>
          <th class="px-3 py-2 text-left">Title</th>
          <th class="px-3 py-2 text-left">CTA</th>
          <th class="px-3 py-2 text-center">Active</th>
          <th class="px-3 py-2 text-right">Actions</th>
        </tr>
      </thead>
      <tbody>
        @forelse($slides as $s)
          <tr class="border-t dark:border-gray-800">
            <td class="px-3 py-2 align-middle">{{ $s->sort_order }}</td>
            <td class="px-3 py-2 align-middle">
              @if($s->image_path)
                <img src="{{ Storage::url($s->image_path) }}" class="h-12 w-20 rounded object-cover">
              @else — @endif
            </td>
            <td class="px-3 py-2 align-middle">{{ $s->title ?? '—' }}</td>
            <td class="px-3 py-2 align-middle">
              @if($s->cta_url)
                <a href="{{ $s->cta_url }}" target="_blank" class="text-indigo-600 hover:underline">
                  {{ $s->cta_label ?? 'Link' }}
                </a>
              @else — @endif
            </td>
            <td class="px-3 py-2 text-center align-middle">{{ $s->is_active ? '✔︎' : '—' }}</td>
            <td class="px-3 py-2 text-right align-middle">
              <a class="text-indigo-600 hover:underline" href="{{ route('admin.slides.edit',$s) }}">Edit</a>
              <form class="ml-3 inline" method="POST" action="{{ route('admin.slides.destroy',$s) }}" onsubmit="return confirm('Eliminare?')">
                @csrf @method('DELETE')
                <button class="text-red-600 hover:underline">Delete</button>
              </form>
            </td>
          </tr>
        @empty
          <tr><td colspan="6" class="px-3 py-6 text-center text-gray-500">Add or create a slide. Empty for now!</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</x-admin-layout>
