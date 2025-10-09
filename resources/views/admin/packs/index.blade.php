<x-admin-layout title="Packs">
  {{-- ✅ Intro box invariato --}}
  <div class="bg-gray-50 p-4 rounded border border-gray-200 dark:bg-gray-800 dark:border-gray-700 text-sm text-gray-600 dark:text-white mb-6">
    Welcome to the Packs management page. Here you can create, edit, and delete packs that bundle your services. Use the "New Pack" button to add a new pack, and click "Edit" next to an existing pack to modify its content or settings.
  </div>

  {{-- Toolbar --}}
  <div class="mb-4 flex items-center justify-between">
    <h2 class="text-lg font-semibold">Packs</h2>
    <a href="{{ route('admin.packs.create') }}"
       class="inline-flex items-center gap-2 rounded-xl bg-[color:var(--accent)] px-3 py-1.5 text-sm font-medium text-white transition hover:opacity-90">
      <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M12 5v14M5 12h14"/></svg>
      Add Pack
    </a>
  </div>

  @if (session('success'))
    <div class="mb-4 rounded border border-green-300 bg-green-50/80 p-3 text-sm text-green-800 dark:border-green-600 dark:bg-green-900/40 dark:text-green-300">
      {{ session('success') }}
    </div>
  @endif

  {{-- Table Desktop --}}
  <div class="hidden overflow-hidden rounded-2xl border border-[color:var(--accent)]/20 bg-white/80 shadow-sm backdrop-blur
              dark:border-[color:var(--accent)]/20 dark:bg-gray-900/70 md:block">
    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
      <thead class="bg-gray-50/70 text-left text-xs font-semibold uppercase text-gray-600 dark:bg-gray-900/60 dark:text-gray-300">
        <tr>
          <th class="px-4 py-3">Cover</th>
          <th class="px-4 py-3">Title</th>
          <th class="px-4 py-3">Category</th>
          <th class="px-4 py-3">Price</th>
          <th class="px-4 py-3">Status</th>
          <th class="px-4 py-3 text-right">Actions</th>
        </tr>
      </thead>

      <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
        @forelse($packs as $p)
          @php
            $map = [
              'indigo'  => 'bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300',
              'emerald' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300',
              'rose'    => 'bg-rose-50 text-rose-700 dark:bg-rose-900/30 dark:text-rose-300',
              'amber'   => 'bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300',
              'sky'     => 'bg-sky-50 text-sky-700 dark:bg-sky-900/30 dark:text-sky-300',
            ];
            $color = optional($p->category)->color ?? 'indigo';
            $cls = $map[$color] ?? 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-200';
          @endphp

          <tr class="transition hover:bg-gray-50/60 dark:hover:bg-black/40">
            <td class="px-4 py-3">
              <div class="h-12 w-16 overflow-hidden rounded-lg ring-1 ring-black/5 dark:ring-white/10">
                @if($p->image_path)
                  <img src="{{ asset('storage/'.$p->image_path) }}" class="h-full w-full object-cover" alt="">
                @else
                  <div class="grid h-full w-full place-items-center text-xs text-gray-400">—</div>
                @endif
              </div>
            </td>

            <td class="px-4 py-3 font-medium text-gray-900 dark:text-gray-100">
              {{ $p->title }}
              <div class="text-xs text-gray-500 dark:text-gray-400">/{{ $p->slug }}</div>
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

            <td class="px-4 py-3 text-gray-700 dark:text-gray-200">
              {{ number_format($p->price_cents/100, 2, ',', '.') }} {{ $p->currency }}
            </td>

            <td class="px-4 py-3">
              <span class="rounded-full px-2.5 py-0.5 text-xs font-medium
                {{ $p->status==='published'
                    ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300'
                    : 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300' }}">
                {{ ucfirst($p->status) }}
              </span>
            </td>

            <td class="px-4 py-3 text-right">
              <a class="text-indigo-600 hover:underline dark:text-indigo-400 mr-3" href="{{ route('admin.packs.edit',$p) }}">Edit</a>
              <form class="inline" method="POST" action="{{ route('admin.packs.destroy',$p) }}" onsubmit="return confirm('Eliminare?')">
                @csrf @method('DELETE')
                <button class="text-red-600 hover:underline dark:text-red-400">Delete</button>
              </form>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="6" class="px-4 py-6 text-center text-sm text-gray-500 dark:text-gray-300">
              No pack found or created.
              <a href="{{ route('admin.packs.create') }}" class="underline">Create one</a>.
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  {{-- Cards Mobile --}}
  <div class="grid gap-3 md:hidden">
    @foreach($packs as $p)
      <div class="rounded-xl border border-[color:var(--accent)]/20 bg-white/80 p-3 shadow-sm backdrop-blur dark:border-[color:var(--accent)]/20 dark:bg-gray-900/70">
        <div class="flex items-center gap-3">
          <div class="h-14 w-20 overflow-hidden rounded-lg ring-1 ring-black/5 dark:ring-white/10">
            @if($p->image_path)
              <img src="{{ asset('storage/'.$p->image_path) }}" class="h-full w-full object-cover" alt="">
            @else
              <div class="grid h-full w-full place-items-center text-xs text-gray-400">—</div>
            @endif
          </div>
          <div class="min-w-0">
            <div class="truncate font-medium">{{ $p->title }}</div>
            <div class="truncate text-xs text-gray-500">/{{ $p->slug }}</div>
          </div>
          <a href="{{ route('admin.packs.edit',$p) }}" class="ml-auto rounded-full border px-3 py-1 text-xs hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800">Edit</a>
        </div>
        <div class="mt-2 flex items-center justify-between text-sm">
          <div>
            @if($p->category)
              @php
                $map = [
                  'indigo'  => 'bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300',
                  'emerald' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300',
                  'rose'    => 'bg-rose-50 text-rose-700 dark:bg-rose-900/30 dark:text-rose-300',
                  'amber'   => 'bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300',
                  'sky'     => 'bg-sky-50 text-sky-700 dark:bg-sky-900/30 dark:text-sky-300',
                ];
                $color = optional($p->category)->color ?? 'indigo';
                $cls = $map[$color] ?? 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-200';
              @endphp
              <span class="rounded-full px-2.5 py-0.5 text-xs font-medium {{ $cls }}">{{ $p->category->name }}</span>
            @else
              <span class="text-xs text-gray-400">—</span>
            @endif
          </div>
          <div class="font-orbitron">
            {{ number_format($p->price_cents/100, 2, ',', '.') }} {{ $p->currency }}
          </div>
        </div>
      </div>
    @endforeach
  </div>

  <div class="mt-4">{{ $packs->links() }}</div>
</x-admin-layout>