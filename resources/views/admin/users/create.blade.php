<x-admin-layout title="Users">
  {{-- Header + actions --}}
  <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <form class="flex w-full max-w-xl items-center gap-2" method="GET" action="{{ route('admin.users.index') }}">
      <input type="text" name="q" value="{{ $q }}" placeholder="Search name or email…"
             class="w-full rounded-xl border border-[color:var(--accent)]/40 bg-white/70 px-3 py-2 shadow-sm outline-none transition
                    focus:border-[color:var(--accent)] focus:ring-2 focus:ring-[color:var(--accent)]/20
                    dark:border-[color:var(--accent)]/30 dark:bg-gray-900 dark:text-gray-100">
      <button class="rounded-xl border px-3 py-2 text-sm transition hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800">Search</button>
      <a href="{{ route('admin.users.index', array_filter(['q'=>$q,'char'=>$char,'buyers'=> request('buyers') ? null : 1])) }}"
         class="rounded-xl border px-3 py-2 text-sm transition hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800">
        {{ request('buyers') ? 'All users' : 'Only buyers' }}
      </a>
    </form>

    <div class="flex items-center gap-2">
      <a href="{{ route('admin.users.export') }}"
         class="rounded-xl border px-3 py-2 text-sm transition hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800">Export CSV</a>
      <a href="{{ route('admin.users.create') }}"
         class="rounded-xl bg-[color:var(--accent)] px-3 py-2 text-sm font-medium text-white shadow-sm transition hover:opacity-90">New</a>
    </div>
  </div>

  {{-- barra A-Z --}}
  <div class="mb-4 flex flex-wrap gap-1 text-xs">
    @foreach($letters as $L)
      <a href="{{ route('admin.users.index', array_filter(['q'=>$q,'buyers'=>request('buyers'), 'char'=>$L])) }}"
         class="rounded-lg border px-2 py-1 {{ request('char')===$L ? 'bg-[color:var(--accent)] text-white border-[color:var(--accent)]' : 'hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800' }}">
        {{ $L }}
      </a>
    @endforeach
    <a href="{{ route('admin.users.index', array_filter(['q'=>$q,'buyers'=>request('buyers')])) }}"
       class="ml-2 rounded-lg border px-2 py-1 hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800">All</a>
  </div>

  @php
    $avatarFallback = 'data:image/svg+xml;utf8,' . rawurlencode('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64"><defs><linearGradient id="g" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#7c3aed"/><stop offset="1" stop-color="#06b6d4"/></linearGradient></defs><circle cx="32" cy="32" r="32" fill="url(#g)"/><circle cx="32" cy="24" r="12" fill="#fff" opacity=".9"/><path d="M12 54a20 20 0 0 1 40 0" fill="#fff" opacity=".9"/></svg>');
    $avatarUrl = function($u) use ($avatarFallback) {
      return $u->profile_photo_url
          ?? ($u->avatar_url ?? null)
          ?? (isset($u->avatar) && filter_var($u->avatar, FILTER_VALIDATE_URL) ? $u->avatar : null)
          ?? (isset($u->avatar_path) && $u->avatar_path ? \Illuminate\Support\Facades\Storage::url($u->avatar_path) : null)
          ?? $avatarFallback;
    };
  @endphp

  {{-- MOBILE --}}
  <div class="sm:hidden space-y-3">
    @forelse($users as $u)
      @php
        $pids = $packIdsByUser[$u->id]  ?? [];
        $cids = $coachIdsByUser[$u->id] ?? [];
        $isBuyer = (count($pids)+count($cids))>0;
      @endphp
      <div class="rounded-2xl border border-[color:var(--accent)]/40 bg-white/70 p-4 shadow-sm backdrop-blur transition
                  dark:border-[color:var(--accent)]/30 dark:bg-gray-900/60">
        <div class="flex items-center gap-3">
          <img src="{{ $avatarUrl($u) }}" class="h-12 w-12 rounded-full object-cover ring-1 ring-black/5 dark:ring-white/10" />
          <div class="min-w-0 flex-1">
            <div class="truncate font-semibold text-gray-900 dark:text-gray-100">{{ $u->name ?: '—' }}</div>
            <div class="truncate text-sm text-gray-600 dark:text-gray-300">{{ $u->email }}</div>
          </div>
          <div class="flex shrink-0 gap-1">
            <a class="rounded-lg border px-2 py-1 text-xs dark:border-gray-700" href="{{ route('admin.users.show',$u) }}">View</a>
            <a class="rounded-lg border px-2 py-1 text-xs dark:border-gray-700" href="{{ route('admin.users.edit',$u) }}">Edit</a>
          </div>
        </div>

        <div class="mt-3 flex flex-wrap items-center gap-1">
          <span class="rounded-full px-2 py-0.5 text-[11px] font-semibold
            {{ $isBuyer ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-200' : 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-200' }}">
            {{ $isBuyer ? 'Buyer' : 'No purchases' }}
          </span>
          @foreach($pids as $id)
            <span class="rounded-full border px-2 py-0.5 text-[11px] dark:border-gray-700">Pack: {{ $packsMap[$id] ?? "#$id" }}</span>
          @endforeach
          @foreach($cids as $id)
            <span class="rounded-full border px-2 py-0.5 text-[11px] dark:border-gray-700">Coach: {{ $coachMap[$id] ?? "#$id" }}</span>
          @endforeach
        </div>
      </div>
    @empty
      <div class="rounded-2xl border border-[color:var(--accent)]/40 p-6 text-center text-sm text-gray-500 dark:border-[color:var(--accent)]/30">No results.</div>
    @endforelse
  </div>

  {{-- DESKTOP --}}
  <div class="hidden overflow-hidden rounded-2xl border border-[color:var(--accent)]/40 dark:border-[color:var(--accent)]/30 sm:block">
    <table class="min-w-full divide-y dark:divide-gray-800">
      <thead class="bg-gray-50 dark:bg-gray-900">
        <tr class="text-left text-xs uppercase text-gray-500">
          <th class="px-4 py-2">User</th>
          <th class="px-4 py-2">Email</th>
          <th class="px-4 py-2">Purchases</th>
          <th class="px-4 py-2 w-28"></th>
        </tr>
      </thead>
      <tbody class="divide-y dark:divide-gray-800">
        @forelse($users as $u)
          @php
            $pids = $packIdsByUser[$u->id]  ?? [];
            $cids = $coachIdsByUser[$u->id] ?? [];
            $isBuyer = (count($pids)+count($cids))>0;
          @endphp
          <tr class="hover:bg-gray-50/60 dark:hover:bg-gray-800/50">
            <td class="px-4 py-2">
              <div class="flex items-center gap-3">
                <img src="{{ $avatarUrl($u) }}" class="h-9 w-9 rounded-full object-cover ring-1 ring-black/5 dark:ring-white/10" />
                <div class="font-medium">{{ $u->name ?: '—' }}</div>
              </div>
            </td>
            <td class="px-4 py-2 text-sm">{{ $u->email }}</td>
            <td class="px-4 py-2">
              <div class="flex flex-wrap items-center gap-1">
                <span class="rounded-full px-2 py-0.5 text-[11px] font-semibold
                  {{ $isBuyer ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-200' : 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-200' }}">
                  {{ $isBuyer ? 'Buyer' : 'No purchases' }}
                </span>
                @foreach($pids as $id)
                  <span class="rounded-full border px-2 py-0.5 text-[11px] dark:border-gray-700">Pack: {{ $packsMap[$id] ?? "#$id" }}</span>
                @endforeach
                @foreach($cids as $id)
                  <span class="rounded-full border px-2 py-0.5 text-[11px] dark:border-gray-700">Coach: {{ $coachMap[$id] ?? "#$id" }}</span>
                @endforeach
              </div>
            </td>
            <td class="px-4 py-2 text-right">
              <a class="rounded-lg border px-2 py-1 text-xs dark:border-gray-700" href="{{ route('admin.users.show',$u) }}">View</a>
              <a class="ml-1 rounded-lg border px-2 py-1 text-xs dark:border-gray-700" href="{{ route('admin.users.edit',$u) }}">Edit</a>
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