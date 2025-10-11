<x-admin-layout title="Users">
  <div class="mb-4 flex items-center justify-between gap-3">
    <form class="flex w-full max-w-xl items-center gap-2">
      <input type="text" name="q" value="{{ $q }}" placeholder="Search name or email…"
             class="w-full rounded border px-3 py-2 dark:bg-gray-900 dark:border-gray-700">
      <button class="rounded border px-3 py-2 dark:border-gray-700">Search</button>
    </form>

    <div class="flex items-center gap-2">
      <a href="{{ route('admin.users.export') }}"
         class="rounded border px-3 py-2 text-sm hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800">Export CSV</a>
      <a href="{{ route('admin.users.create') }}"
         class="rounded bg-[var(--accent)] px-3 py-2 text-sm text-white">New</a>
    </div>
  </div>

  {{-- barra A-Z stile contatti --}}
  <div class="mb-4 flex flex-wrap gap-1 text-xs">
    @foreach($letters as $L)
      <a href="{{ route('admin.users.index', array_filter(['q'=>$q,'char'=>$L])) }}"
         class="rounded border px-2 py-1 {{ request('char')===$L ? 'bg-[var(--accent)] text-white' : 'hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800' }}">
        {{ $L }}
      </a>
    @endforeach
    <a href="{{ route('admin.users.index', array_filter(['q'=>$q])) }}"
       class="ml-2 rounded border px-2 py-1 hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800">All</a>
  </div>

  <div class="overflow-hidden rounded-xl border dark:border-gray-800">
    <table class="min-w-full divide-y dark:divide-gray-800">
      <thead class="bg-gray-50 dark:bg-gray-900">
        <tr class="text-left text-xs uppercase text-gray-500">
          <th class="px-4 py-2">Name</th>
          <th class="px-4 py-2">Email</th>
          <th class="px-4 py-2">Role</th>
          <th class="px-4 py-2 w-24"></th>
        </tr>
      </thead>
      <tbody class="divide-y dark:divide-gray-800">
        @forelse($users as $u)
          <tr class="hover:bg-gray-50/60 dark:hover:bg-gray-800/50">
            <td class="px-4 py-2 font-medium">{{ $u->name }}</td>
            <td class="px-4 py-2 text-sm">{{ $u->email }}</td>
            <td class="px-4 py-2 text-sm">{{ $u->role }}</td>
            <td class="px-4 py-2 text-right">
              <a class="rounded border px-2 py-1 text-xs dark:border-gray-700" href="{{ route('admin.users.show',$u) }}">View</a>
              <a class="ml-1 rounded border px-2 py-1 text-xs dark:border-gray-700" href="{{ route('admin.users.edit',$u) }}">Edit</a>
            </td>
          </tr>
        @empty
          <tr><td colspan="4" class="px-4 py-6 text-center text-sm text-gray-500">No results.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div class="mt-4">{{ $users->links() }}</div>
</x-admin-layout>
