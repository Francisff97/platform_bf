<x-admin-layout title="Hero sections">
  <div class="mb-4 flex items-center justify-between">
    <h2 class="text-lg font-semibold">Heroes</h2>
    <a href="{{ route('admin.heroes.create') }}"
       class="rounded bg-[var(--accent)] px-3 py-1.5 text-white text-sm hover:opacity-90">
      Add new
    </a>
  </div>

  {{-- ===== MOBILE: CARD LIST ===== --}}
  <div class="grid grid-cols-1 gap-4 md:hidden">
    @forelse($heroes as $h)
      <div class="overflow-hidden rounded-xl border bg-white shadow-sm ring-1 ring-black/5 dark:border-gray-800 dark:bg-gray-900 dark:ring-white/10">
        <div class="flex items-start gap-3 p-3">
          {{-- preview --}}
          <div class="h-16 w-28 overflow-hidden rounded-lg bg-gray-100 ring-1 ring-black/5 dark:bg-gray-800 dark:ring-white/10 shrink-0">
            @if($h->image_path)
              <img src="{{ Storage::url($h->image_path) }}" class="h-full w-full object-cover" alt="">
            @endif
          </div>

          <div class="min-w-0 flex-1">
            <div class="flex items-center gap-2">
              <span class="text-xs rounded-full px-2 py-0.5 bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                {{ $h->page }}
              </span>
              <span class="text-xs rounded-full px-2 py-0.5
                {{ $h->is_active ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-200' : 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300' }}">
                {{ $h->is_active ? 'Active' : 'Hidden' }}
              </span>
            </div>

            <div class="mt-1 line-clamp-1 font-medium text-gray-900 dark:text-gray-100">
              {{ $h->title ?: '—' }}
            </div>

            @if($h->subtitle)
              <div class="line-clamp-1 text-xs text-gray-500 dark:text-gray-400">{{ $h->subtitle }}</div>
            @endif
          </div>
        </div>

        <div class="flex items-center justify-between border-t p-3 text-xs text-gray-500 dark:border-gray-800">
          <div class="flex items-center gap-3">
            <span>h: {{ $h->height_css ?: '—' }}</span>
            <span class="hidden xs:inline">•</span>
            <span class="line-clamp-1">overlay: {{ $h->overlay ?: '—' }}</span>
          </div>
          <div class="flex items-center gap-3 text-sm">
            <a href="{{ route('admin.heroes.edit',$h) }}" class="text-indigo-600 hover:underline">Edit</a>
            <form method="POST" action="{{ route('admin.heroes.destroy',$h) }}" class="inline" onsubmit="return confirm('Eliminare?')">
              @csrf @method('DELETE')
              <button class="text-rose-600 hover:underline">Delete</button>
            </form>
          </div>
        </div>
      </div>
    @empty
      <div class="rounded-xl border bg-white p-4 text-center text-gray-500 shadow-sm dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300">
        No heroes yet.
      </div>
    @endforelse
  </div>

  {{-- ===== DESKTOP: TABLE ===== --}}
  <div class="hidden overflow-hidden rounded-xl border bg-white shadow-sm dark:bg-gray-900 dark:border-gray-800 md:block">
    <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-800">
      <thead class="bg-gray-50 dark:bg-gray-950">
        <tr>
          <th class="px-4 py-3 text-left">Page</th>
          <th class="px-4 py-3 text-left">Preview</th>
          <th class="px-4 py-3 text-left">Title</th>
          <th class="px-4 py-3 text-left">Height</th>
          <th class="px-4 py-3 text-left">Overlay</th>
          <th class="px-4 py-3 text-center">Active</th>
          <th class="px-4 py-3 text-right">Actions</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
        @forelse($heroes as $h)
          <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/60">
            <td class="px-4 py-3">
              <span class="rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-semibold text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                {{ $h->page }}
              </span>
            </td>
            <td class="px-4 py-3">
              @if($h->image_path)
                <img src="{{ Storage::url($h->image_path) }}" class="h-12 w-20 rounded object-cover" alt="">
              @else
                <div class="h-12 w-20 rounded bg-gray-200 dark:bg-gray-800"></div>
              @endif
            </td>
            <td class="px-4 py-3">
              <div class="font-medium text-gray-900 dark:text-gray-100">{{ $h->title ?: '—' }}</div>
              @if($h->subtitle)
                <div class="line-clamp-1 text-xs text-gray-500 dark:text-gray-400">{{ $h->subtitle }}</div>
              @endif
            </td>
            <td class="px-4 py-3 text-gray-700 dark:text-gray-300">{{ $h->height_css ?: '—' }}</td>
            <td class="px-4 py-3 text-gray-700 dark:text-gray-300">{{ $h->overlay ?: '—' }}</td>
            <td class="px-4 py-3 text-center">
              @if($h->is_active)
                <span class="rounded-full bg-indigo-50 px-2.5 py-0.5 text-xs font-semibold text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-200">Active</span>
              @else
                <span class="rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-semibold text-gray-700 dark:bg-gray-800 dark:text-gray-300">Hidden</span>
              @endif
            </td>
            <td class="px-4 py-3 text-right">
              <a href="{{ route('admin.heroes.edit',$h) }}" class="text-indigo-600 hover:underline mr-3">Edit</a>
              <form method="POST" action="{{ route('admin.heroes.destroy',$h) }}" class="inline" onsubmit="return confirm('Eliminare?')">
                @csrf @method('DELETE')
                <button class="text-rose-600 hover:underline">Delete</button>
              </form>
            </td>
          </tr>
        @empty
          <tr><td colspan="7" class="px-4 py-6 text-center text-gray-500 dark:text-gray-300">No heroes yet.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

  @if(method_exists($heroes,'links'))
    <div class="mt-4">{{ $heroes->links() }}</div>
  @endif
</x-admin-layout>