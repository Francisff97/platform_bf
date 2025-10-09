<x-admin-layout title="Categories">
  <div class="mb-4 flex items-center justify-between">
    <h2 class="text-lg font-semibold">Categories</h2>
    <a href="{{ route('admin.categories.create') }}"
       class="rounded bg-[var(--accent)] px-3 py-1.5 text-sm text-white hover:opacity-90">
       Add new
    </a>
  </div>

  <div class="overflow-hidden rounded-2xl border bg-white shadow-sm dark:border-gray-700 dark:bg-gray-900">
    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
      <thead class="bg-gray-50 text-left text-xs font-semibold uppercase text-gray-500 dark:bg-gray-950 dark:text-gray-300">
        <tr>
          <th class="px-4 py-3">Name</th>
          <th class="px-4 py-3">Slug</th>
          <th class="px-4 py-3">Color</th>
          <th class="px-4 py-3 text-right">Action</th>
        </tr>
      </thead>

      <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
        @foreach($categories as $c)
          <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/70">
            <td class="px-4 py-3 font-medium text-gray-900 dark:text-gray-100">{{ $c->name }}</td>
            <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">/{{ $c->slug }}</td>
            <td class="px-4 py-3">
              @php $color = $c->color; @endphp
              @if(\Illuminate\Support\Str::startsWith($color, '#'))
                <span class="inline-flex items-center gap-2 text-sm">
                  <span class="h-3 w-3 rounded-full" style="background: {{ $color }}"></span>
                  <code>{{ $color }}</code>
                </span>
              @else
                <code class="text-sm">{{ $color ?? '—' }}</code>
              @endif
            </td>
            <td class="px-4 py-3 text-right">
              <a class="mr-3 text-indigo-600 hover:underline" href="{{ route('admin.categories.edit',$c) }}">Edit</a>
              <form class="inline" method="POST" action="{{ route('admin.categories.destroy',$c) }}" onsubmit="return confirm('Eliminare?')">
                @csrf @method('DELETE')
                <button class="text-red-600 hover:underline">Delete</button>
              </form>
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>

  <div class="mt-4">{{ $categories->links() }}</div>
</x-admin-layout>