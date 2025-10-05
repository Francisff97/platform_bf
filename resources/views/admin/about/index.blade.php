<x-admin-layout title="About – Sections">
  <div class="mb-4 flex items-center justify-between">
    <h1 class="text-xl font-bold">About sections</h1>
    <a href="{{ route('admin.about.create') }}" class="rounded bg-[var(--accent)] px-3 py-1.5 text-white">Add section</a>
  </div>

  <div class="overflow-hidden rounded-xl border bg-white dark:bg-gray-800 dark:border-gray-700">
    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
      <thead class="bg-gray-50 dark:bg-gray-900">
        <tr>
          <th class="px-3 py-2 text-left">#</th>
          <th class="px-3 py-2 text-left">Layout</th>
          <th class="px-3 py-2 text-left">Title</th>
          <th class="px-3 py-2 text-center">Featured</th>
          <th class="px-3 py-2 text-center">Active</th>
          <th class="px-3 py-2 text-right">Pos</th>
          <th class="px-3 py-2 text-right"></th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
        @forelse($sections as $s)
          <tr>
            <td class="px-3 py-2">{{ $s->id }}</td>
            <td class="px-3 py-2">{{ $s->layout }}</td>
            <td class="px-3 py-2">{{ $s->title ?: '—' }}</td>
            <td class="px-3 py-2 text-center">
              @if($s->featured)
                <span class="rounded bg-emerald-100 px-2 py-0.5 text-xs text-emerald-700">Yes</span>
              @else
                <span class="rounded bg-gray-100 px-2 py-0.5 text-xs text-gray-600">No</span>
              @endif
            </td>
            <td class="px-3 py-2 text-center">
              @if($s->is_active)
                <span class="rounded bg-indigo-100 px-2 py-0.5 text-xs text-indigo-700">Active</span>
              @else
                <span class="rounded bg-gray-100 px-2 py-0.5 text-xs text-gray-600">Hidden</span>
              @endif
            </td>
            <td class="px-3 py-2 text-right">{{ $s->position }}</td>
            <td class="px-3 py-2 text-right">
              <a href="{{ route('admin.about.edit', $s) }}" class="text-indigo-600 hover:underline mr-3">Edit</a>
              <form method="POST" action="{{ route('admin.about.destroy', $s) }}" class="inline"
                    onsubmit="return confirm('Delete this section?')">
                @csrf @method('DELETE')
                <button class="text-red-600 hover:underline">Delete</button>
              </form>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="7" class="px-3 py-6 text-center text-gray-500 dark:text-gray-300">
              No sections yet.
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</x-admin-layout>