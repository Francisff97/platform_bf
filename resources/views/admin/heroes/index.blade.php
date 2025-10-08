<x-admin-layout title="Hero sections">
  <div class="bg-gray-50 p-4 rounded border border-gray-200 dark:bg-gray-800 dark:border-gray-700 text-sm text-gray-600 dark:text-white mb-6">
    Welcome to the Heroes management page. Here you can create, edit, and delete hero sections for different pages of your website. Use the "New" button to add a new hero section, and click "Edit" next to an existing hero to modify its content or settings.
  </div>
  <div class="mb-4 flex items-center justify-between">
    <h2 class="text-lg font-semibold">Heroes</h2>
    <a href="{{ route('admin.heroes.create') }}" class="rounded bg-[var(--accent)] px-3 py-1.5 text-white text-sm">Add new</a>
  </div>

  <div class="overflow-hidden rounded border dark:border-gray-800">
    <table class="min-w-full text-sm">
      <thead class="bg-gray-50 dark:bg-gray-900">
        <tr><th class="px-3 py-2 text-left">Page</th><th class="px-3 py-2">Title</th><th class="px-3 py-2">Active</th><th class="px-3 py-2 text-right">Actions</th></tr>
      </thead>
      <tbody>
        @foreach($heroes as $h)
          <tr class="border-t dark:border-gray-800">
            <td class="px-3 py-2">{{ $h->page }}</td>
            <td class="px-3 py-2">{{ $h->title }}</td>
            <td class="px-3 py-2">{{ $h->is_active ? '✔︎' : '—' }}</td>
            <td class="px-3 py-2 text-right">
              <a class="text-indigo-600" href="{{ route('admin.heroes.edit',$h) }}">Edit</a>
              <form class="inline" method="POST" action="{{ route('admin.heroes.destroy',$h) }}" onsubmit="return confirm('Eliminare?')">
                @csrf @method('DELETE') <button class="ml-3 text-red-600">Delete</button>
              </form>
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</x-admin-layout>
