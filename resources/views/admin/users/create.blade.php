{{-- resources/views/admin/users/create.blade.php --}}
<x-admin-layout title="Create user">
  <form method="POST" action="{{ route('admin.users.store') }}" class="grid gap-6">
    @csrf

    {{-- Card: Identity --}}
    <div class="rounded-3xl border border-gray-200 p-5 shadow-sm ring-1 ring-black/5 dark:border-gray-700 dark:bg-gray-900">
      <h2 class="mb-4 font-semibold text-gray-900 dark:text-gray-100">Identity</h2>

      <div class="grid gap-4 sm:grid-cols-2">
        {{-- Name --}}
        <div>
          <label class="text-sm font-medium">Name</label>
          <input
            name="name"
            value="{{ old('name') }}"
            class="mt-1 w-full rounded-xl border border-[color:var(--accent)]/40 bg-white/70 px-3 py-2 shadow-sm outline-none transition
                   focus:border-[color:var(--accent)] focus:ring-2 focus:ring-[color:var(--accent)]/20
                   dark:border-[color:var(--accent)]/30 dark:bg-gray-900 dark:text-gray-100">
          @error('name')
            <div class="mt-1 text-xs text-red-600">{{ $message }}</div>
          @enderror
        </div>

        {{-- Email --}}
        <div>
          <label class="text-sm font-medium">Email</label>
          <input
            name="email" type="email"
            value="{{ old('email') }}"
            class="mt-1 w-full rounded-xl border border-[color:var(--accent)]/40 bg-white/70 px-3 py-2 shadow-sm outline-none transition
                   focus:border-[color:var(--accent)] focus:ring-2 focus:ring-[color:var(--accent)]/20
                   dark:border-[color:var(--accent)]/30 dark:bg-gray-900 dark:text-gray-100">
          @error('email')
            <div class="mt-1 text-xs text-red-600">{{ $message }}</div>
          @enderror
        </div>

        {{-- Password (optional) --}}
        <div class="sm:col-span-2">
          <label class="text-sm font-medium">Password (optional)</label>
          <input
            name="password" type="password"
            placeholder="Leave empty to auto-generate"
            class="mt-1 w-full rounded-xl border border-[color:var(--accent)]/40 bg-white/70 px-3 py-2 shadow-sm outline-none transition
                   focus:border-[color:var(--accent)] focus:ring-2 focus:ring-[color:var(--accent)]/20
                   dark:border-[color:var(--accent)]/30 dark:bg-gray-900 dark:text-gray-100">
          @error('password')
            <div class="mt-1 text-xs text-red-600">{{ $message }}</div>
          @enderror
        </div>
      </div>
    </div>

    {{-- Card: Ownership --}}
    <div class="rounded-3xl border border-gray-200 p-5 shadow-sm ring-1 ring-black/5 dark:border-gray-700 dark:bg-gray-900">
      <div class="mb-4 flex items-center justify-between">
        <h2 class="font-semibold text-gray-900 dark:text-gray-100">Ownership</h2>

        {{-- Buyer toggle (opzionale, se hai la colonna) --}}
        <label class="inline-flex select-none items-center gap-2">
          <input
            type="checkbox" name="is_buyer" value="1" @checked(old('is_buyer'))
            class="h-4 w-4 rounded border-gray-300 text-[color:var(--accent)] focus:ring-[color:var(--accent)]">
          <span class="text-sm">Buyer</span>
        </label>
      </div>

      <div class="grid gap-4 sm:grid-cols-2">
        {{-- Packs --}}
        <div>
          <label class="text-sm font-medium">Packs</label>
          <select
            name="pack_ids[]" multiple
            class="mt-1 w-full min-h-[180px] rounded-xl border border-[color:var(--accent)]/40 bg-white/70 px-3 py-2 shadow-sm outline-none
                   focus:border-[color:var(--accent)] focus:ring-2 focus:ring-[color:var(--accent)]/20
                   dark:border-[color:var(--accent)]/30 dark:bg-gray-900 dark:text-gray-100">
            @foreach($packs as $p)
              <option value="{{ $p->id }}" @selected(collect(old('pack_ids',[]))->contains($p->id))>
                {{ $p->title }}
              </option>
            @endforeach
          </select>
          @error('pack_ids')
            <div class="mt-1 text-xs text-red-600">{{ $message }}</div>
          @enderror
        </div>

        {{-- Coaches --}}
        <div>
          <label class="text-sm font-medium">Coaches</label>
          <select
            name="coach_ids[]" multiple
            class="mt-1 w-full min-h-[180px] rounded-xl border border-[color:var(--accent)]/40 bg-white/70 px-3 py-2 shadow-sm outline-none
                   focus:border-[color:var(--accent)] focus:ring-2 focus:ring-[color:var(--accent)]/20
                   dark:border-[color:var(--accent)]/30 dark:bg-gray-900 dark:text-gray-100">
            @foreach($coaches as $c)
              <option value="{{ $c->id }}" @selected(collect(old('coach_ids',[]))->contains($c->id))>
                {{ $c->name }}
              </option>
            @endforeach
          </select>
          @error('coach_ids')
            <div class="mt-1 text-xs text-red-600">{{ $message }}</div>
          @enderror
        </div>
      </div>
    </div>

    {{-- Footer actions --}}
    <div class="flex items-center gap-2">
      <button
        class="rounded-xl bg-[color:var(--accent)] px-4 py-2 text-white shadow-sm transition hover:opacity-90">
        Create
      </button>
      <a
        href="{{ route('admin.users.index') }}"
        class="rounded-xl border px-4 py-2 transition hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800">
        Cancel
      </a>
    </div>
  </form>
</x-admin-layout>