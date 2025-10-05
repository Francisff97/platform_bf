<x-admin-layout title="Packs">
  <div class="bg-gray-50 p-4 rounded border border-gray-200 dark:bg-gray-800 dark:border-gray-700 text-sm text-gray-600 dark:text-white mb-6">
    Welcome to the Packs management page. Here you can create, edit, and delete packs that bundle your services. Use the "New Pack" button to add a new pack, and click "Edit" next to an existing pack to modify its content or settings.
  </div>
  <div class="mb-4 flex items-center justify-between">
    <h2 class="text-lg font-semibold">Packs</h2>
    <a href="{{ route('admin.packs.create') }}"
       class="rounded bg-[var(--accent)] px-3 py-1.5 text-white text-sm hover:opacity-90">
       Add Pack
    </a>
  </div>

  @if (session('success'))
    <div class="mb-4 rounded border border-green-300 bg-green-50 p-3 text-sm text-green-800">
      {{ session('success') }}
    </div>
  @endif

  <div class="overflow-hidden rounded-xl border bg-white shadow-sm dark:bg-gray-800 dark:border-gray-700">
    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
      <thead class="bg-gray-50 dark:bg-gray-900 text-left text-xs font-semibold uppercase text-gray-500">
        <tr>
          <th class="px-4 py-3">Cover</th>
          <th class="px-4 py-3">Title</th>
          <th class="px-4 py-3">Category</th>
          <th class="px-4 py-3">Price</th>
          <th class="px-4 py-3">Status</th>
          <th class="px-4 py-3 text-right">Actions</th>
        </tr>
      </thead>

      <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
        @forelse($packs as $p)
          @php
            $map = [
              'indigo'  => 'bg-indigo-50 text-indigo-700',
              'emerald' => 'bg-emerald-50 text-emerald-700',
              'rose'    => 'bg-rose-50 text-rose-700',
              'amber'   => 'bg-amber-50 text-amber-700',
              'sky'     => 'bg-sky-50 text-sky-700',
            ];
            // colore safe (default indigo)
            $color = optional($p->category)->color ?? 'indigo';
            $cls   = $map[$color] ?? 'bg-gray-100 text-gray-700';
          @endphp

          <tr class="hover:bg-gray-50 dark:hover:bg-gray-900">
            <td class="px-4 py-3">
              @if($p->image_path)
                <img src="{{ asset('storage/'.$p->image_path) }}" class="h-12 w-12 rounded object-cover">
              @else
                <div class="h-12 w-12 rounded bg-gray-200"></div>
              @endif
            </td>

            <td class="px-4 py-3 font-medium text-gray-900 dark:text-gray-100">
              {{ $p->title }}
              <div class="text-xs text-gray-500">/{{ $p->slug }}</div>
            </td>

            <td class="px-4 py-3">
              @if($p->category)
                <span class="rounded-full px-2.5 py-0.5 text-xs font-medium {{ $cls }}">
                  {{ $p->category->name }}
                </span>
              @else
                <span class="text-xs text-gray-400">—</span>
              @endif
            </td>

            <td class="px-4 py-3">
              {{ number_format($p->price_cents/100, 2, ',', '.') }} {{ $p->currency }}
            </td>

            <td class="px-4 py-3">
              <span class="rounded-full px-2.5 py-0.5 text-xs font-medium
                {{ $p->status==='published' ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-100 text-gray-700' }}">
                {{ ucfirst($p->status) }}
              </span>
            </td>

            <td class="px-4 py-3 text-right">
              <a class="text-indigo-600 hover:underline mr-3" href="{{ route('admin.packs.edit',$p) }}">Edit</a>
              <form class="inline" method="POST" action="{{ route('admin.packs.destroy',$p) }}" onsubmit="return confirm('Eliminare?')">
                @csrf @method('DELETE')
                <button class="text-red-600 hover:underline">Delete</button>
              </form>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="6" class="px-4 py-6 text-center text-sm text-gray-500">
              No pack find or created. <a href="{{ route('admin.packs.create') }}" class="underline">Creane uno</a>.
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div class="mt-4">{{ $packs->links() }}</div>
</x-admin-layout>