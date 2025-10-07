<x-admin-layout title="Partners">
  <div class="mb-4 flex items-center justify-between">
    <h1 class="text-xl font-bold">Partners</h1>
    <a href="{{ route('admin.partners.create') }}"
       class="rounded-xl bg-[color:var(--accent)] px-4 py-2 text-white">New Partner</a>
  </div>

  @if (session('success'))
    <div class="mb-4 rounded border border-green-300 bg-green-50 px-3 py-2 text-sm text-green-800">
      {{ session('success') }}
    </div>
  @endif

  <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-4">
    @foreach($partners as $p)
      <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
        <div class="flex items-center gap-3">
          <div class="h-14 w-14 overflow-hidden rounded-full ring-1 ring-black/5 dark:ring-white/10">
            @if($p->logo_path)
              <img src="{{ Storage::url($p->logo_path) }}" class="h-full w-full object-cover" alt="{{ $p->name }}">
            @else
              <div class="grid h-full w-full place-items-center text-xs text-gray-400">No logo</div>
            @endif
          </div>
          <div>
            <div class="font-medium">{{ $p->name }}</div>
            <div class="text-xs text-gray-500">#{{ $p->order }} · {{ ucfirst($p->status) }}</div>
          </div>
        </div>

        <div class="mt-4 flex items-center gap-2">
          <a href="{{ route('admin.partners.edit',$p) }}"
             class="rounded border px-3 py-1.5 text-sm dark:border-gray-700">Edit</a>
          <form method="POST" action="{{ route('admin.partners.destroy',$p) }}"
                onsubmit="return confirm('Delete partner?')">
            @csrf @method('DELETE')
            <button class="rounded border border-red-300 px-3 py-1.5 text-sm text-red-600">Delete</button>
          </form>
        </div>
      </div>
    @endforeach
  </div>

  <div class="mt-6">{{ $partners->links() }}</div>
</x-admin-layout>
