<x-admin-layout title="Builders">
  <div class="mb-6 rounded border border-gray-200 bg-gray-50 p-4 text-sm text-gray-600 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
    Welcome to the Builders management page. Here you can create, edit, and delete builders and their associated skills. Use the "Add Builder" button to add a new builder, and click "Edit" next to an existing builder to modify their details or skills.
  </div>

  <div class="mb-4 flex items-center justify-between">
    <h2 class="text-lg font-semibold">Builders</h2>
    <a href="{{ route('admin.builders.create') }}"
       class="rounded bg-[var(--accent)] px-3 py-1.5 text-sm text-white hover:opacity-90">
       Add Builder
    </a>
  </div>

  <div class="overflow-hidden rounded-xl border bg-white shadow-sm dark:border-gray-700 dark:bg-gray-900">
    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
      <thead class="bg-gray-50 text-left text-xs font-semibold uppercase text-gray-500 dark:bg-gray-900 dark:text-gray-300">
        <tr>
          <th class="px-4 py-3">Builder</th>
          <th class="px-4 py-3">Team</th>
          <th class="px-4 py-3">Skills</th>
          <th class="px-4 py-3 text-right">Actions</th>
        </tr>
      </thead>

      <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
        @foreach($builders as $b)
          <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/70">
            <td class="px-4 py-3">
              <div class="flex items-center gap-3">
                <div class="h-10 w-10 overflow-hidden rounded-full bg-gray-200 ring-1 ring-black/5 dark:bg-gray-800 dark:ring-white/10">
                  @if($b->image_path)
                    <img src="{{ Storage::url($b->image_path) }}" class="h-full w-full object-cover" alt="{{ $b->name }}">
                  @endif
                </div>
                <div class="min-w-0">
                  <div class="truncate font-medium text-gray-900 dark:text-gray-100">{{ $b->name }}</div>
                  <div class="truncate text-xs text-gray-500">/{{ $b->slug }}</div>
                </div>
              </div>
            </td>

            <td class="px-4 py-3 text-gray-700 dark:text-gray-300">{{ $b->team ?? '—' }}</td>

            <td class="px-4 py-3">
              @if($b->skills)
                <div class="flex max-w-[420px] flex-wrap gap-1.5">
                  @foreach($b->skills as $s)
                    <span class="rounded-full bg-gray-100 px-2 py-0.5 text-[11px] font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-200">{{ $s }}</span>
                  @endforeach
                </div>
              @else
                <span class="text-sm text-gray-400">—</span>
              @endif
            </td>

            <td class="px-4 py-3 text-right">
              <a class="mr-3 text-indigo-600 hover:underline" href="{{ route('admin.builders.edit',$b) }}">Edit</a>
              <form class="inline" method="POST" action="{{ route('admin.builders.destroy',$b) }}" onsubmit="return confirm('Eliminare?')">
                @csrf @method('DELETE')
                <button class="text-red-600 hover:underline">Delete</button>
              </form>
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>

  <div class="mt-4">{{ $builders->links() }}</div>
</x-admin-layout>