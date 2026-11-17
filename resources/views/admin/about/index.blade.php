<x-admin-layout title="About – Sections">
  <div class="mb-4 flex items-center justify-between">
    <h1 class="text-xl font-bold">About sections</h1>
    <a href="{{ route('admin.about.create') }}" class="rounded bg-[var(--accent)] px-3 py-1.5 text-white hover:opacity-90">
      Add section
    </a>
  </div>

  {{-- ===== MOBILE: CARD LIST ===== --}}
  <div class="grid grid-cols-1 gap-4 md:hidden">
    @forelse($sections as $s)
      <div class="overflow-hidden rounded-xl border bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <div class="flex items-start gap-3 p-3">
          {{-- thumb --}}
          <div class="h-16 w-24 overflow-hidden rounded-lg bg-gray-100 ring-1 ring-black/5 dark:bg-gray-800 dark:ring-white/10">
            @if($s->image_path)
              <img src="{{ Storage::url($s->image_path) }}" alt="" class="h-full w-full object-cover">
            @endif
          </div>

          {{-- testo --}}
          <div class="min-w-0 flex-1">
            <div class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
              <span>#{{ $s->id }}</span>
              <span>•</span>
              <span class="uppercase">{{ $s->layout }}</span>
            </div>
            <div class="mt-0.5 line-clamp-1 font-medium text-gray-900 dark:text-gray-100">
              {{ $s->title ?: '—' }}
            </div>
            @if($s->body)
              <div class="line-clamp-2 text-xs text-gray-500 dark:text-gray-400">
                {{ strip_tags($s->body) }}
              </div>
            @endif

            {{-- badges --}}
            <div class="mt-2 flex flex-wrap items-center gap-2">
              @if($s->featured)
                <span class="rounded-full bg-emerald-50 px-2.5 py-0.5 text-[11px] font-semibold text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-200">Featured</span>
              @else
                <span class="rounded-full bg-gray-100 px-2.5 py-0.5 text-[11px] font-semibold text-gray-700 dark:bg-gray-800 dark:text-gray-300">Not Featured</span>
              @endif

              @if($s->is_active)
                <span class="rounded-full bg-indigo-50 px-2.5 py-0.5 text-[11px] font-semibold text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-200">Active</span>
              @else
                <span class="rounded-full bg-gray-100 px-2.5 py-0.5 text-[11px] font-semibold text-gray-700 dark:bg-gray-800 dark:text-gray-300">Hidden</span>
              @endif>

              <span class="rounded-full bg-gray-100 px-2.5 py-0.5 text-[11px] font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                Pos: {{ $s->position }}
              </span>
            </div>
          </div>
        </div>

        {{-- actions --}}
        <div class="flex items-center justify-between gap-2 border-t p-3 text-sm dark:border-gray-800">
          <a href="{{ route('admin.about.edit',$s) }}" class="text-indigo-600 hover:underline">Edit</a>
          <form method="POST" action="{{ route('admin.about.destroy',$s) }}"
                onsubmit="return confirm('Delete this section?')" class="inline">
            @csrf @method('DELETE')
            <button class="text-rose-600 hover:underline">Delete</button>
          </form>
        </div>
      </div>
    @empty
      <div class="rounded-xl border bg-white p-4 text-center text-gray-500 shadow-sm dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300">
        No sections yet.
      </div>
    @endforelse
  </div>

  {{-- ===== DESKTOP: TABLE ===== --}}
  <div class="hidden overflow-hidden rounded-xl border bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900 md:block">
    <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-800">
      <thead class="bg-gray-50 dark:bg-gray-950">
        <tr>
          <th class="px-4 py-3 text-left">#</th>
          <th class="px-4 py-3 text-left">Preview</th>
          <th class="px-4 py-3 text-left">Layout</th>
          <th class="px-4 py-3 text-left">Title</th>
          <th class="px-4 py-3 text-center">Featured</th>
          <th class="px-4 py-3 text-center">Active</th>
          <th class="px-4 py-3 text-right">Pos</th>
          <th class="px-4 py-3 text-right">Actions</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
        @forelse($sections as $s)
          <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/60">
            <td class="px-4 py-3">#{{ $s->id }}</td>
            <td class="px-4 py-3">
              @if($s->image_path)
                <img src="{{ Storage::url($s->image_path) }}" class="h-12 w-20 rounded object-cover" alt="">
              @else
                <div class="h-12 w-20 rounded bg-gray-200 dark:bg-gray-800"></div>
              @endif
            </td>
            <td class="px-4 py-3">{{ $s->layout }}</td>
            <td class="px-4 py-3">
              <div class="font-medium text-gray-900 dark:text-gray-100">{{ $s->title ?: '—' }}</div>
              @if($s->body)
                <div class="line-clamp-1 text-xs text-gray-500 dark:text-gray-400">{{ strip_tags($s->body) }}</div>
              @endif
            </td>
            <td class="px-4 py-3 text-center">
              @if($s->featured)
                <span class="rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-semibold text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-200">Yes</span>
              @else
                <span class="rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-semibold text-gray-700 dark:bg-gray-800 dark:text-gray-300">No</span>
              @endif
            </td>
            <td class="px-4 py-3 text-center">
              @if($s->is_active)
                <span class="rounded-full bg-indigo-50 px-2.5 py-0.5 text-xs font-semibold text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-200">Active</span>
              @else
                <span class="rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-semibold text-gray-700 dark:bg-gray-800 dark:text-gray-300">Hidden</span>
              @endif
            </td>
            <td class="px-4 py-3 text-right">{{ $s->position }}</td>
            <td class="px-4 py-3 text-right">
              <a href="{{ route('admin.about.edit',$s) }}" class="mr-3 text-indigo-600 hover:underline">Edit</a>
              <form method="POST" action="{{ route('admin.about.destroy',$s) }}" class="inline"
                    onsubmit="return confirm('Delete this section?')">
                @csrf @method('DELETE')
                <button class="text-rose-600 hover:underline">Delete</button>
              </form>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="8" class="px-4 py-6 text-center text-gray-500 dark:text-gray-300">
              No sections yet.
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  {{-- pagination --}}
  @if(method_exists($sections,'links'))
    <div class="mt-4">{{ $sections->links() }}</div>
  @endif
</x-admin-layout>