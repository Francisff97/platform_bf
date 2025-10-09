<x-admin-layout title="Hero sections">
  <div class="mb-4 flex items-center justify-between">
    <h2 class="text-lg font-semibold">Heroes</h2>
    <a href="{{ route('admin.heroes.create') }}"
       class="rounded bg-[var(--accent)] px-3 py-1.5 text-sm text-white hover:opacity-90">
       Add new
    </a>
  </div>

  <div class="overflow-hidden rounded-2xl border bg-white shadow-sm dark:border-gray-700 dark:bg-gray-900">
    <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-700">
      <thead class="bg-gray-50 text-left font-semibold uppercase text-gray-500 dark:bg-gray-950 dark:text-gray-300">
        <tr>
          <th class="px-4 py-3">Page</th>
          <th class="px-4 py-3">Title</th>
          <th class="px-4 py-3">Active</th>
          <th class="px-4 py-3 text-right">Actions</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
        @foreach($heroes as $h)
          <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/70">
            <td class="px-4 py-3 font-medium text-gray-900 dark:text-gray-100">{{ $h->page }}</td>
            <td class="px-4 py-3 text-gray-700 dark:text-gray-300">{{ $h->title ?? '—' }}</td>
            <td class="px-4 py-3">
              <span class="rounded-full px-2.5 py-0.5 text-xs font-medium
                           {{ $h->is_active ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-200'
                                            : 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300' }}">
                {{ $h->is_active ? 'Active' : '—' }}
              </span>
            </td>
            <td class="px-4 py-3 text-right">
              <a class="mr-3 text-indigo-600 hover:underline" href="{{ route('admin.heroes.edit',$h) }}">Edit</a>
              <form class="inline" method="POST" action="{{ route('admin.heroes.destroy',$h) }}" onsubmit="return confirm('Eliminare?')">
                @csrf @method('DELETE')
                <button class="text-red-600 hover:underline">Delete</button>
              </form>
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>

  <div class="mt-4">{{ $heroes->links() }}</div>
</x-admin-layout>