<x-admin-layout title="Coaches">
  <div class="bg-gray-50 p-4 rounded border border-gray-200 dark:bg-gray-800 dark:border-gray-700 text-sm text-gray-600 dark:text-white mb-6">
    Welcome to the Coaches management page. Here you can create, edit, and delete coaches who provide expertise in various areas. Use the "Add Coach" button to add a new coach, and click "Edit" next to an existing coach to modify their details or skills.
  </div>
  <div class="mb-4 flex items-center justify-between">
    <h2 class="text-lg font-semibold">Coaches</h2>
    <a href="{{ route('admin.coaches.create') }}" class="rounded bg-[var(--accent)] px-3 py-1.5 text-white text-sm hover:opacity-90">Add Coach</a>
  </div>

  <div class="overflow-hidden rounded-xl border bg-white shadow-sm dark:bg-gray-800 dark:border-gray-700">
    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
      <thead class="bg-gray-50 dark:bg-gray-900 text-left text-xs font-semibold uppercase text-gray-500">
        <tr>
          <th class="px-4 py-3">Name</th>
          <th class="px-4 py-3">Team</th>
          <th class="px-4 py-3">Skills</th>
          <th class="px-4 py-3 text-right">Actions</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
        @foreach($coaches as $c)
          <tr class="hover:bg-gray-50 dark:hover:bg-gray-900">
            <td class="px-4 py-3 font-medium text-gray-900 dark:text-gray-100">{{ $c->name }}</td>
            <td class="px-4 py-3 text-gray-600 dark:text-gray-300">{{ $c->team ?? '—' }}</td>
            <td class="px-4 py-3 text-gray-600 dark:text-gray-300">
              @if($c->skills) {{ implode(', ', $c->skills) }} @else — @endif
            </td>
            <td class="px-4 py-3 text-right">
              <a class="text-indigo-600 hover:underline mr-3" href="{{ route('admin.coaches.edit',$c) }}">Edit</a>
              <form class="inline" method="POST" action="{{ route('admin.coaches.destroy',$c) }}" onsubmit="return confirm('Eliminare?')">
                @csrf @method('DELETE')
                <button class="text-red-600 hover:underline">Delete</button>
              </form>
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>

  <div class="mt-4">{{ $coaches->links() }}</div>
</x-admin-layout>
