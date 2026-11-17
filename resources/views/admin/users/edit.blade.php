<x-admin-layout title="Edit user">
  <form method="POST" action="{{ route('admin.users.update',$user) }}" class="grid gap-6">
    @csrf
    @method('PUT')

    <div class="rounded-3xl border border-gray-200 p-5 shadow-sm ring-1 ring-black/5 dark:border-gray-700 dark:bg-gray-900">
      <h2 class="mb-4 font-semibold text-gray-900 dark:text-gray-100">Identity</h2>

      <div class="grid gap-4 sm:grid-cols-2">
        <div>
          <label class="text-sm font-medium">Name</label>
          <input name="name" value="{{ old('name',$user->name) }}"
                 class="mt-1 w-full rounded-xl border border-[color:var(--accent)]/40 bg-white/70 px-3 py-2 shadow-sm outline-none transition
                        focus:border-[color:var(--accent)] focus:ring-2 focus:ring-[color:var(--accent)]/20
                        dark:border-[color:var(--accent)]/30 dark:bg-gray-900 dark:text-gray-100">
          @error('name') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
        </div>

        <div>
          <label class="text-sm font-medium">Email (read-only)</label>
          <input value="{{ $user->email }}" disabled
                 class="mt-1 w-full rounded-xl border border-transparent bg-gray-50 px-3 py-2 opacity-70 dark:bg-gray-800 dark:text-gray-300">
        </div>
      </div>
    </div>

    <div class="rounded-3xl border border-gray-200 p-5 shadow-sm ring-1 ring-black/5 dark:border-gray-700 dark:bg-gray-900">
      <h2 class="mb-4 font-semibold text-gray-900 dark:text-gray-100">Ownership</h2>

      <label class="inline-flex items-center gap-2">
        <input type="checkbox" name="is_buyer" value="1"
               @checked(old('is_buyer', property_exists($user,'is_buyer') ? (bool)$user->is_buyer : false))
               class="h-4 w-4 rounded border-gray-300 text-[color:var(--accent)] focus:ring-[color:var(--accent)]">
        <span class="text-sm">Buyer</span>
      </label>

      <div class="mt-4 grid gap-4 sm:grid-cols-2">
        <div>
          <label class="text-sm font-medium">Packs</label>
          <select name="pack_ids[]" multiple
                  class="mt-1 w-full min-h-[200px] rounded-xl border border-[color:var(--accent)]/40 bg-white/70 px-3 py-2 shadow-sm outline-none
                         focus:border-[color:var(--accent)] focus:ring-2 focus:ring-[color:var(--accent)]/20
                         dark:border-[color:var(--accent)]/30 dark:bg-gray-900 dark:text-gray-100">
            @foreach($packs as $p)
              <option value="{{ $p->id }}" @selected(in_array($p->id,$ownedPacks))>{{ $p->title }}</option>
            @endforeach
          </select>
        </div>

        <div>
          <label class="text-sm font-medium">Coaches</label>
          <select name="coach_ids[]" multiple
                  class="mt-1 w-full min-h-[200px] rounded-xl border border-[color:var(--accent)]/40 bg-white/70 px-3 py-2 shadow-sm outline-none
                         focus:border-[color:var(--accent)] focus:ring-2 focus:ring-[color:var(--accent)]/20
                         dark:border-[color:var(--accent)]/30 dark:bg-gray-900 dark:text-gray-100">
            @foreach($coaches as $c)
              <option value="{{ $c->id }}" @selected(in_array($c->id,$ownedCoaches))>{{ $c->name }}</option>
            @endforeach
          </select>
        </div>
      </div>
    </div>

    <div class="flex items-center gap-2">
      <button class="rounded-xl bg-[color:var(--accent)] px-4 py-2 text-white shadow-sm transition hover:opacity-90">Save</button>
      <a href="{{ route('admin.users.show',$user) }}" class="rounded-xl border px-4 py-2 transition hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800">Cancel</a>
    </div>
  </form>
</x-admin-layout>