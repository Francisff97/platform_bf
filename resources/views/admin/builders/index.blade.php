<x-admin-layout title="Builders">
  <div class="bg-gray-50 p-4 rounded border border-gray-200 dark:bg-gray-800 dark:border-gray-700 text-sm text-gray-600 dark:text-white mb-6">
    Welcome to the Builders management page. Here you can create, edit, and delete builders and their associated skills. Use the "Add Builder" button to add a new builder, and click "Edit" next to an existing builder to modify their details or skills.
  </div>
<div class="mb-4 flex items-center justify-between">
    <h2 class="text-lg font-semibold">Builders</h2>
    <a href="{{ route('admin.builders.create') }}"
       class="rounded bg-[var(--accent)] px-3 py-1.5 text-white text-sm hover:opacity-90">
       Add Builder
    </a>
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
        @foreach($builders as $b)
          <tr class="hover:bg-gray-50 dark:hover:bg-gray-900">
            <td class="px-4 py-3 font-medium text-gray-900 dark:text-gray-100">{{ $b->name }}</td>
            <td class="px-4 py-3 text-gray-600 dark:text-gray-300">{{ $b->team ?? '—' }}</td>
            <td class="px-4 py-3 text-gray-600 dark:text-gray-300">
              @if($b->skills) {{ implode(', ', $b->skills) }} @else — @endif
            </td>
            <td class="px-4 py-3 text-right">
              <a class="text-indigo-600 hover:underline mr-3" href="{{ route('admin.builders.edit',$b) }}">Edit</a>
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
