<x-admin-layout title="Builders">
  <div class="mb-4 flex items-center justify-between">
    <h2 class="text-lg font-semibold">Builders</h2>
    <a href="{{ route('admin.builders.create') }}"
       class="rounded bg-[var(--accent)] px-3 py-1.5 text-white text-sm hover:opacity-90">
       Add Builder
    </a>
  </div>

  {{-- ===== MOBILE: CARD LIST ===== --}}
  <div class="grid grid-cols-1 gap-4 md:hidden">
    @forelse($builders as $b)
      <div class="overflow-hidden rounded-xl border bg-white shadow-sm ring-1 ring-black/5 dark:border-gray-800 dark:bg-gray-900 dark:ring-white/10">
        <div class="flex items-start gap-3 p-3">
          {{-- avatar --}}
          <div class="h-12 w-12 overflow-hidden rounded-full bg-gray-100 ring-1 ring-black/5 dark:bg-gray-800 dark:ring-white/10">
            @if($b->image_path)
              <img src="{{ asset('storage/'.$b->image_path) }}" class="h-full w-full object-cover" alt="">
            @endif
          </div>
          <div class="min-w-0 flex-1">
            <div class="line-clamp-1 font-medium text-gray-900 dark:text-gray-100">{{ $b->name }}</div>
            <div class="text-xs text-gray-500 dark:text-gray-400">{{ $b->team ?? '—' }}</div>
            @if($b->skills)
              <div class="mt-1 line-clamp-1 text-xs text-gray-600 dark:text-gray-300">
                {{ is_array($b->skills) ? implode(', ', $b->skills) : $b->skills }}
              </div>
            @endif
          </div>
        </div>
        <div class="flex items-center justify-end gap-3 border-t p-3 text-sm dark:border-gray-800">
          <a class="text-indigo-600 hover:underline" href="{{ route('admin.builders.edit',$b) }}">Edit</a>
          <form class="inline" method="POST" action="{{ route('admin.builders.destroy',$b) }}" onsubmit="return confirm('Eliminare?')">
            @csrf @method('DELETE')
            <button class="text-rose-600 hover:underline">Delete</button>
          </form>
        </div>
      </div>
    @empty
      <div class="rounded-xl border bg-white p-4 text-center text-gray-500 shadow-sm dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300">
        No builders found.
      </div>
    @endforelse
  </div>

  {{-- ===== DESKTOP: TABLE ===== --}}
  <div class="hidden overflow-hidden rounded-xl border bg-white shadow-sm dark:bg-gray-900 dark:border-gray-800 md:block">
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
          <tr class="hover:bg-gray-50 dark:hover:bg-gray-900/60">
            <td class="px-4 py-3 font-medium text-gray-900 dark:text-gray-100">{{ $b->name }}</td>
            <td class="px-4 py-3 text-gray-600 dark:text-gray-300">{{ $b->team ?? '—' }}</td>
            <td class="px-4 py-3 text-gray-600 dark:text-gray-300">
              @if($b->skills) {{ is_array($b->skills) ? implode(', ', $b->skills) : $b->skills }} @else — @endif
            </td>
            <td class="px-4 py-3 text-right">
              <a class="text-indigo-600 hover:underline mr-3" href="{{ route('admin.builders.edit',$b) }}">Edit</a>
              <form class="inline" method="POST" action="{{ route('admin.builders.destroy',$b) }}" onsubmit="return confirm('Eliminare?')">
                @csrf @method('DELETE')
                <button class="text-rose-600 hover:underline">Delete</button>
              </form>
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>

  @if(method_exists($builders,'links'))
    <div class="mt-4">{{ $builders->links() }}</div>
  @endif
</x-admin-layout>