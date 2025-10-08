<x-admin-layout title="SEO Pages">
  <div class="flex items-center justify-between mb-4">
    <h1 class="text-xl font-bold">SEO Pages</h1>
    <a href="{{ route('admin.seo.pages.create') }}" class="rounded bg-[var(--accent)] px-3 py-1.5 text-white">New</a>
  </div>

  <div class="rounded border dark:border-gray-800 overflow-hidden">
    <table class="w-full text-sm">
      <thead class="bg-gray-50 dark:bg-gray-800/60">
        <tr>
          <th class="px-3 py-2 text-left">Route</th>
          <th class="px-3 py-2 text-left">Path</th>
          <th class="px-3 py-2 text-left">Title</th>
          <th class="px-3 py-2 text-left">Description</th>
          <th class="px-3 py-2"></th>
        </tr>
      </thead>
      <tbody>
        @forelse($pages as $p)
          <tr class="border-t dark:border-gray-800">
            <td class="px-3 py-2">{{ $p->route_name }}</td>
            <td class="px-3 py-2">{{ $p->path }}</td>
            <td class="px-3 py-2 truncate max-w-[220px]">{{ $p->meta_title }}</td>
            <td class="px-3 py-2 truncate max-w-[260px]">{{ $p->meta_description }}</td>
            <td class="px-3 py-2 text-right">
              <a href="{{ route('admin.seo.pages.edit',$p) }}" class="underline">Edit</a>
            </td>
          </tr>
        @empty
          <tr><td colspan="5" class="px-3 py-6 text-center text-gray-500">No items.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div class="mt-4">{{ $pages->links() }}</div>
</x-admin-layout>
