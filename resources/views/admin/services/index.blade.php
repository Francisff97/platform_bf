<x-admin-layout title="Services">
  {{-- Intro box in stile "glass" --}}
  <div class="mx-auto mb-6 max-w-5xl rounded-2xl border border-[color:var(--accent)]/30 bg-white/70 p-4 text-sm text-gray-700 shadow-sm backdrop-blur
              dark:border-[color:var(--accent)]/30 dark:bg-gray-900/70 dark:text-gray-200">
    Welcome to the Services management page. Here you can create, edit, and delete services offered by your company.
    Use the “New Service” button to add a new service, and click “Edit” next to an existing service to modify its details or status.
  </div>

  {{-- Toolbar --}}
  <div class="mb-4 flex items-center justify-between">
    <h2 class="text-lg font-semibold">Services</h2>
    <a href="{{ route('admin.services.create') }}"
       class="inline-flex items-center gap-2 rounded-xl bg-[color:var(--accent)] px-3 py-1.5 text-sm font-medium text-white transition hover:opacity-90">
      <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M12 5v14M5 12h14"/></svg>
      Add Service
    </a>
  </div>

  {{-- ===== CARDS (mobile) ===== --}}
  <div class="grid grid-cols-1 gap-4 md:hidden">
    @foreach($services as $s)
      <div class="group overflow-hidden rounded-2xl border border-[color:var(--accent)]/20 bg-white/80 p-4 shadow-sm backdrop-blur transition
                  hover:-translate-y-0.5 hover:shadow-md dark:border-[color:var(--accent)]/20 dark:bg-gray-900/70">
        <div class="flex items-start gap-3">
          <div class="h-16 w-24 overflow-hidden rounded-lg ring-1 ring-black/5 dark:ring-white/10">
            @if($s->image_path)
              <x-img :src="Storage::url($s->image_path)" class="h-full w-full object-cover" :alt="$s->name" />
            @else
              <div class="grid h-full w-full place-items-center text-xs text-gray-400">—</div>
            @endif
          </div>

          <div class="min-w-0 flex-1">
            <div class="flex items-center justify-between gap-2">
              <h3 class="truncate text-base font-semibold text-gray-900 dark:text-gray-100">{{ $s->name }}</h3>
              <span class="shrink-0 rounded-full px-2.5 py-0.5 text-xs font-medium
                           {{ $s->status==='published'
                                ? 'bg-green-50 text-green-700 dark:bg-green-900/30 dark:text-green-300'
                                : 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-200' }}">
                {{ $s->status }}
              </span>
            </div>

            <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">
              Order: <span class="font-medium text-gray-700 dark:text-gray-200">{{ $s->order }}</span>
            </div>

            <div class="mt-3 flex items-center justify-end gap-3">
              <a class="text-[color:var(--accent)] hover:underline" href="{{ route('admin.services.edit',$s) }}">Edit</a>
              <form class="inline" method="POST" action="{{ route('admin.services.destroy',$s) }}" onsubmit="return confirm('Eliminare?')">
                @csrf @method('DELETE')
                <button class="text-red-600 hover:underline">Delete</button>
              </form>
            </div>
          </div>
        </div>
      </div>
    @endforeach
  </div>

  {{-- ===== TABLE (desktop) ===== --}}
  <div class="hidden overflow-hidden rounded-2xl border border-[color:var(--accent)]/20 bg-white/80 shadow-sm backdrop-blur
              dark:border-[color:var(--accent)]/20 dark:bg-gray-900/70 md:block">
    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
      <thead class="bg-gray-50/70 text-left text-xs font-semibold uppercase text-gray-600 dark:bg-gray-900/60 dark:text-gray-300">
        <tr>
          <th class="px-4 py-3">Image</th>
          <th class="px-4 py-3">Name</th>
          <th class="px-4 py-3">Status</th>
          <th class="px-4 py-3">Order</th>
          <th class="px-4 py-3 text-right">Actions</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
        @foreach($services as $s)
          <tr class="transition hover:bg-gray-50/60 dark:hover:bg-black/40">
            <td class="px-4 py-3">
              <div class="h-12 w-20 overflow-hidden rounded-lg ring-1 ring-black/5 dark:ring-white/10">
                @if($s->image_path)
                  <x-img :src="Storage::url($s->image_path)" class="h-full w-full object-cover" :alt="$s->name" />
                @else
                  <div class="grid h-full w-full place-items-center text-xs text-gray-400">—</div>
                @endif
              </div>
            </td>

            <td class="px-4 py-3 font-medium text-gray-900 dark:text-gray-100">
              <div class="line-clamp-1">{{ $s->name }}</div>
            </td>

            <td class="px-4 py-3">
              <span class="rounded-full px-2.5 py-0.5 text-xs font-medium
                           {{ $s->status==='published'
                                ? 'bg-green-50 text-green-700 dark:bg-green-900/30 dark:text-green-300'
                                : 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-200' }}">
                {{ $s->status }}
              </span>
            </td>

            <td class="px-4 py-3 text-gray-700 dark:text-gray-200">
              {{ $s->order }}
            </td>

            <td class="px-4 py-3 text-right">
              <a class="text-[color:var(--accent)] hover:underline" href="{{ route('admin.services.edit',$s) }}">Edit</a>
              <span class="mx-2 text-gray-300 dark:text-gray-600">|</span>
              <form class="inline" method="POST" action="{{ route('admin.services.destroy',$s) }}" onsubmit="return confirm('Eliminare?')">
                @csrf @method('DELETE')
                <button class="text-red-600 hover:underline">Delete</button>
              </form>
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>

  <div class="mt-4">{{ $services->links() }}</div>
</x-admin-layout>