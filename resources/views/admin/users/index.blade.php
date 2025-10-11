{{-- resources/views/admin/users/index.blade.php --}}
<x-admin-layout title="Users">
  <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <form class="flex w-full max-w-xl items-center gap-2" method="GET" action="{{ route('admin.users.index') }}">
      <input type="text" name="q" value="{{ $q }}" placeholder="Search name or email…"
             class="w-full rounded border px-3 py-2 dark:bg-gray-900 dark:border-gray-700" />
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

  @php
    // Fallback avatar SVG inline (tondo)
    $avatarFallback = 'data:image/svg+xml;utf8,' . rawurlencode(
      '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64">
         <defs><linearGradient id="g" x1="0" y1="0" x2="1" y2="1">
           <stop offset="0" stop-color="#7c3aed"/><stop offset="1" stop-color="#06b6d4"/></linearGradient></defs>
         <circle cx="32" cy="32" r="32" fill="url(#g)"/>
         <circle cx="32" cy="24" r="12" fill="#fff" opacity=".9"/>
         <path d="M12 54a20 20 0 0 1 40 0" fill="#fff" opacity=".9"/>
       </svg>'
    );
    // Helper closure per ottenere l’URL dell’avatar
    $avatarUrl = function($u) use ($avatarFallback) {
      return $u->profile_photo_url
          ?? ($u->avatar_url ?? null)
          ?? (isset($u->avatar) && filter_var($u->avatar, FILTER_VALIDATE_URL) ? $u->avatar : null)
          ?? (isset($u->avatar_path) && $u->avatar_path ? \Illuminate\Support\Facades\Storage::url($u->avatar_path) : null)
          ?? $avatarFallback;
    };
  @endphp

  {{-- ===== MOBILE: lista a card (accent border) ===== --}}
  <div class="sm:hidden space-y-3">
    @forelse($users as $u)
      <div class="rounded-xl border border-[color:var(--accent)]/40 bg-white/70 p-3 shadow-sm backdrop-blur
                  dark:border-[color:var(--accent)]/30 dark:bg-gray-900/60">
        <div class="flex items-center gap-3">
          <img src="{{ $avatarUrl($u) }}" alt="" class="h-12 w-12 rounded-full object-cover ring-1 ring-black/5 dark:ring-white/10" />
          <div class="min-w-0 flex-1">
            <div class="truncate font-semibold text-gray-900 dark:text-gray-100">{{ $u->name ?: '—' }}</div>
            <div class="truncate text-sm text-gray-600 dark:text-gray-300">{{ $u->email }}</div>
            <div class="mt-1 inline-flex items-center rounded-full border border-[color:var(--accent)]/40 px-2 py-0.5 text-[11px] font-medium text-gray-700
                        dark:border-[color:var(--accent)]/30 dark:text-gray-200">
              {{ $u->role }}
            </div>
          </div>
          <div class="flex shrink-0 flex-col gap-1">
            <a class="rounded border px-2 py-1 text-xs dark:border-gray-700" href="{{ route('admin.users.show',$u) }}">View</a>
            <a class="rounded border px-2 py-1 text-xs dark:border-gray-700" href="{{ route('admin.users.edit',$u) }}">Edit</a>
          </div>
        </div>
      </div>
    @empty
      <div class="rounded-xl border border-[color:var(--accent)]/40 p-6 text-center text-sm text-gray-500
                  dark:border-[color:var(--accent)]/30">No results.</div>
    @endforelse
  </div>

  {{-- ===== DESKTOP: tabella classica ===== --}}
  <div class="hidden sm:block overflow-hidden rounded-xl border border-[color:var(--accent)]/40 dark:border-[color:var(--accent)]/30">
    <table class="min-w-full divide-y dark:divide-gray-800">
      <thead class="bg-gray-50 dark:bg-gray-900">
        <tr class="text-left text-xs uppercase text-gray-500">
          <th class="px-4 py-2">User</th>
          <th class="px-4 py-2">Email</th>
          <th class="px-4 py-2">Role</th>
          <th class="px-4 py-2 w-28"></th>
        </tr>
      </thead>
      <tbody class="divide-y dark:divide-gray-800">
        @forelse($users as $u)
          <tr class="hover:bg-gray-50/60 dark:hover:bg-gray-800/50">
            <td class="px-4 py-2">
              <div class="flex items-center gap-3">
                <img src="{{ $avatarUrl($u) }}" alt="" class="h-9 w-9 rounded-full object-cover ring-1 ring-black/5 dark:ring-white/10" />
                <div class="font-medium">{{ $u->name ?: '—' }}</div>
              </div>
            </td>
            <td class="px-4 py-2 text-sm">{{ $u->email }}</td>
            <td class="px-4 py-2">
              <span class="inline-flex items-center rounded-full border border-[color:var(--accent)]/40 px-2 py-0.5 text-[11px] font-medium
                           dark:border-[color:var(--accent)]/30">
                {{ $u->role }}
              </span>
            </td>
            <td class="px-4 py-2 text-right">
              <a class="rounded border px-2 py-1 text-xs dark:border-gray-700" href="{{ route('admin.users.show',$u) }}">View</a>
              <a class="ml-1 rounded border px-2 py-1 text-xs dark:border-gray-700" href="{{ route('admin.users.edit',$u) }}">Edit</a>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="4" class="px-4 py-6 text-center text-sm text-gray-500">No results.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div class="mt-4">{{ $users->links() }}</div>
</x-admin-layout>
