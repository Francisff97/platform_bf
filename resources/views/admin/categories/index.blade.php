<x-admin-layout title="Categories">
  <div class="mb-4 flex items-center justify-between">
    <h2 class="text-lg font-semibold">Categories</h2>
    <a href="{{ route('admin.categories.create') }}"
       class="rounded bg-[var(--accent)] px-3 py-1.5 text-white text-sm hover:opacity-90">
       Add new
    </a>
  </div>

  {{-- ===== MOBILE: CARD LIST ===== --}}
  <div class="grid grid-cols-1 gap-4 md:hidden">
    @forelse($categories as $c)
      <div class="overflow-hidden rounded-xl border bg-white shadow-sm ring-1 ring-black/5 dark:border-gray-800 dark:bg-gray-900 dark:ring-white/10">
        <div class="flex items-start gap-3 p-3">
          {{-- Color badge --}}
          <div class="mt-0.5">
            @if(Str::startsWith($c->color, '#'))
              <span class="inline-block h-4 w-4 rounded-full ring-1 ring-black/10" style="background: {{ $c->color }}"></span>
            @else
              <span class="inline-flex items-center rounded-full bg-gray-100 px-2 py-0.5 text-[10px] uppercase tracking-wide text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                {{ $c->color ?: '—' }}
              </span>
            @endif
          </div>

          <div class="min-w-0 flex-1">
            <div class="line-clamp-1 font-medium text-gray-900 dark:text-gray-100">{{ $c->name }}</div>
            <div class="text-xs text-gray-500 dark:text-gray-400">/{{ $c->slug }}</div>
          </div>
        </div>
        <div class="flex items-center justify-end gap-3 border-t p-3 text-sm dark:border-gray-800">
          <a class="text-indigo-600 hover:underline" href="{{ route('admin.categories.edit',$c) }}">Edit</a>
          <form class="inline" method="POST" action="{{ route('admin.categories.destroy',$c) }}" onsubmit="return confirm('Eliminare?')">
            @csrf @method('DELETE')
            <button class="text-rose-600 hover:underline">Delete</button>
          </form>
        </div>
      </div>
    @empty
      <div class="rounded-xl border bg-white p-4 text-center text-gray-500 shadow-sm dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300">
        No categories yet.
      </div>
    @endforelse
  </div>

  {{-- ===== DESKTOP: TABLE ===== --}}
  <div class="hidden overflow-hidden rounded-xl border bg-white shadow-sm dark:bg-gray-900 dark:border-gray-800 md:block">
    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
      <thead class="bg-gray-50 text-left text-xs font-semibold uppercase text-gray-500 dark:bg-gray-950">
        <tr>
          <th class="px-4 py-3">Name</th>
          <th class="px-4 py-3">Slug</th>
          <th class="px-4 py-3">Color</th>
          <th class="px-4 py-3 text-right">Action</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
        @foreach($categories as $c)
          <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/60">
            <td class="px-4 py-3 font-medium text-gray-900 dark:text-gray-100">{{ $c->name }}</td>
            <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-300">{{ $c->slug }}</td>
            <td class="px-4 py-3">
              @if(Str::startsWith($c->color, '#'))
                <span class="inline-flex items-center gap-2 text-sm">
                  <span class="h-3 w-3 rounded-full ring-1 ring-black/10" style="background: {{ $c->color }}"></span>
                  <code>{{ $c->color }}</code>
                </span>
              @else
                <code class="text-sm text-gray-700 dark:text-gray-300">{{ $c->color ?? '—' }}</code>
              @endif
            </td>
            <td class="px-4 py-3 text-right">
              <a class="text-indigo-600 hover:underline mr-3" href="{{ route('admin.categories.edit',$c) }}">Edit</a>
              <form class="inline" method="POST" action="{{ route('admin.categories.destroy',$c) }}" onsubmit="return confirm('Eliminare?')">
                @csrf @method('DELETE')
                <button class="text-rose-600 hover:underline">Delete</button>
              </form>
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>

  @if(method_exists($categories,'links'))
    <div class="mt-4">{{ $categories->links() }}</div>
  @endif
</x-admin-layout>