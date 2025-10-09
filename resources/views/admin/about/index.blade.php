<x-admin-layout title="About – Sections">
  <div class="mb-4 flex items-center justify-between">
    <h1 class="text-xl font-bold">About sections</h1>
    <a href="{{ route('admin.about.create') }}" class="rounded bg-[var(--accent)] px-3 py-1.5 text-white hover:opacity-90">
      Add section
    </a>
  </div>

  <div class="overflow-hidden rounded-xl border bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
    <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-800">
      <thead class="bg-gray-50 dark:bg-gray-950">
        <tr>
          <th class="px-4 py-3 text-left">#</th>
          <th class="px-4 py-3 text-left">Preview</th>
          <th class="px-4 py-3 text-left">Layout</th>
          <th class="px-4 py-3 text-left">Title</th>
          <th class="px-4 py-3 text-center">Featured</th>
          <th class="px-4 py-3 text-center">Active</th>
          <th class="px-4 py-3 text-right">Pos</th>
          <th class="px-4 py-3 text-right">Actions</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
        @forelse($sections as $s)
          <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/60">
            <td class="px-4 py-3">#{{ $s->id }}</td>

            <td class="px-4 py-3">
              @if($s->image_path)
                <img src="{{ Storage::url($s->image_path) }}" class="h-12 w-20 rounded object-cover" alt="">
              @else
                <div class="h-12 w-20 rounded bg-gray-200 dark:bg-gray-800"></div>
              @endif
            </td>

            <td class="px-4 py-3">{{ $s->layout }}</td>

            <td class="px-4 py-3">
              <div class="font-medium text-gray-900 dark:text-gray-100">{{ $s->title ?: '—' }}</div>
              @if($s->body)
                <div class="line-clamp-1 text-xs text-gray-500 dark:text-gray-400">{{ strip_tags($s->body) }}</div>
              @endif
            </td>

            <td class="px-4 py-3 text-center">
              @if($s->featured)
                <span class="rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-semibold text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-200">Yes</span>
              @else
                <span class="rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-semibold text-gray-700 dark:bg-gray-800 dark:text-gray-300">No</span>
              @endif
            </td>

            <td class="px-4 py-3 text-center">
              @if($s->is_active)
                <span class="rounded-full bg-indigo-50 px-2.5 py-0.5 text-xs font-semibold text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-200">Active</span>
              @else
                <span class="rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-semibold text-gray-700 dark:bg-gray-800 dark:text-gray-300">Hidden</span>
              @endif
            </td>

            <td class="px-4 py-3 text-right">{{ $s->position }}</td>

            <td class="px-4 py-3 text-right">
              <a href="{{ route('admin.about.edit',$s) }}" class="text-indigo-600 hover:underline mr-3">Edit</a>
              <form method="POST" action="{{ route('admin.about.destroy',$s) }}" class="inline"
                    onsubmit="return confirm('Delete this section?')">
                @csrf @method('DELETE')
                <button class="text-rose-600 hover:underline">Delete</button>
              </form>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="8" class="px-4 py-6 text-center text-gray-500 dark:text-gray-300">
              No sections yet.
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  {{-- se usi paginate() --}}
  @if(method_exists($sections,'links'))
    <div class="mt-4">{{ $sections->links() }}</div>
  @endif
</x-admin-layout>