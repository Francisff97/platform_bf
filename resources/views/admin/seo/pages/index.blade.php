<x-admin-layout title="SEO Pages">
  <div class="mb-4 flex items-center justify-between">
    <h1 class="text-xl font-bold">SEO Pages</h1>
    {{-- opzionale: <a href="{{ route('admin.seo.pages.create') }}" class="rounded bg-[var(--accent)] px-3 py-1.5 text-white">New</a> --}}
  </div>

  {{-- Cards (mobile) --}}
  <div class="grid gap-4 sm:hidden">
    @forelse($pages as $p)
      <div class="group rounded-2xl border bg-white/80 p-4 shadow-sm backdrop-blur
                  hover:-translate-y-0.5 hover:shadow-md
                  dark:border-gray-800 dark:bg-gray-900/70">
        <div class="text-xs text-gray-500 dark:text-gray-400">Route</div>
        <div class="truncate text-sm font-semibold">{{ $p->route_name ?: '—' }}</div>

        <div class="mt-2 text-xs text-gray-500 dark:text-gray-400">Path</div>
        <div class="truncate text-sm">{{ $p->path ?: '—' }}</div>

        <div class="mt-2 text-xs text-gray-500 dark:text-gray-400">Title</div>
        <div class="truncate text-sm font-medium">{{ $p->meta_title ?: '—' }}</div>

        <div class="mt-2 text-xs text-gray-500 dark:text-gray-400">Description</div>
        <div class="line-clamp-2 text-sm">{{ $p->meta_description ?: '—' }}</div>

        <div class="mt-3 flex justify-end">
          <a href="{{ route('admin.seo.pages.edit',$p) }}"
             class="inline-flex items-center rounded-lg border px-3 py-1.5 text-xs
                    hover:bg-gray-50 dark:hover:bg-gray-800 dark:border-gray-700">
            Edit
          </a>
        </div>
      </div>
    @empty
      <div class="rounded-xl border p-6 text-center text-sm text-gray-500 dark:border-gray-800">
        No items.
      </div>
    @endforelse
  </div>

  {{-- Table (md+) --}}
  <div class="hidden overflow-hidden rounded-2xl border bg-white/70 shadow-sm backdrop-blur dark:border-gray-800 dark:bg-gray-900/70 sm:block">
    <table class="w-full text-sm">
      <thead class="bg-gray-50 dark:bg-gray-900">
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
          <tr class="border-t hover:bg-gray-50/60 dark:border-gray-800 dark:hover:bg-gray-800/50">
            <td class="px-3 py-2 align-top">{{ $p->route_name }}</td>
            <td class="px-3 py-2 align-top">{{ $p->path }}</td>
            <td class="px-3 py-2 align-top max-w-[320px] truncate">{{ $p->meta_title }}</td>
            <td class="px-3 py-2 align-top max-w-[420px] truncate">{{ $p->meta_description }}</td>
            <td class="px-3 py-2 align-top text-right">
              <a href="{{ route('admin.seo.pages.edit',$p) }}" class="text-indigo-600 hover:underline">Edit</a>
            </td>
          </tr>
        @empty
          <tr><td colspan="5" class="px-3 py-6 text-center text-gray-500 dark:text-gray-300">No items.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

  @if(method_exists($pages,'links'))
    <div class="mt-4">{{ $pages->links() }}</div>
  @endif
</x-admin-layout>