<x-admin-layout title="Hero sections">
  <div class="bg-gray-50 p-4 rounded border border-gray-200 dark:bg-gray-800 dark:border-gray-700 text-sm text-gray-600 dark:text-white mb-6">
    Welcome to the Heroes management page. Here you can create, edit, and delete hero sections for different pages of your website. Use the "New" button to add a new hero section, and click "Edit" next to an existing hero to modify its content or settings.
  </div>

  <div class="mb-4 flex items-center justify-between">
    <h2 class="text-lg font-semibold">Heroes</h2>
    <a href="{{ route('admin.heroes.create') }}" class="rounded bg-[var(--accent)] px-3 py-1.5 text-white text-sm">Add new</a>
  </div>

  <div class="overflow-hidden rounded-xl border bg-white shadow-sm dark:bg-gray-900 dark:border-gray-800">
    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
      <thead class="bg-gray-50 dark:bg-gray-950 text-left text-xs font-semibold uppercase text-gray-500">
        <tr>
          <th class="px-4 py-3">Preview</th>
          <th class="px-4 py-3">Page</th>
          <th class="px-4 py-3">Title</th>
          <th class="px-4 py-3">Active</th>
          <th class="px-4 py-3 text-right">Actions</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
        @forelse($heroes as $h)
          <tr class="hover:bg-gray-50 dark:hover:bg-gray-800">
            <td class="px-4 py-3">
              <div class="relative h-12 w-20 overflow-hidden rounded">
                @if($h->image_path)
                  <img src="{{ Storage::url($h->image_path) }}" class="absolute inset-0 h-full w-full object-cover" alt="">
                @else
                  <div class="absolute inset-0 bg-gray-200 dark:bg-gray-700"></div>
                @endif
                {{-- overlay applicato come css tailwind salvato --}}
                <div class="absolute inset-0 bg-gradient-to-b {{ $h->overlay }}"></div>
              </div>
            </td>
            <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $h->page }}</td>
            <td class="px-4 py-3 font-medium text-gray-900 dark:text-gray-100">{{ $h->title ?: '—' }}</td>
            <td class="px-4 py-3">
              <span class="rounded-full px-2.5 py-0.5 text-xs font-medium {{ $h->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-100 text-gray-700' }}">
                {{ $h->is_active ? 'Active' : 'Disabled' }}
              </span>
            </td>
            <td class="px-4 py-3 text-right">
              <a class="text-indigo-600 hover:underline mr-3" href="{{ route('admin.heroes.edit',$h) }}">Edit</a>
              <form class="inline" method="POST" action="{{ route('admin.heroes.destroy',$h) }}" onsubmit="return confirm('Eliminare?')">
                @csrf @method('DELETE')
                <button class="text-red-600 hover:underline">Delete</button>
              </form>
            </td>
          </tr>
        @empty
          <tr><td colspan="5" class="px-4 py-6 text-center text-sm text-gray-500">No heroes yet.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

  {{-- Paginate solo se è un paginator --}}
  @if(method_exists($heroes, 'links'))
    <div class="mt-4">{{ $heroes->links() }}</div>
  @endif
</x-admin-layout>