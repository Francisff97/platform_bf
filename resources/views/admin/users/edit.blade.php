<x-admin-layout title="Edit user">
  <form method="POST" action="{{ route('admin.users.update',$user) }}" class="grid gap-6">
    @csrf
    @method('PUT')

    <div class="grid gap-4 sm:grid-cols-2">
      <div>
        <label class="text-sm font-medium">Name</label>
        <input name="name" value="{{ old('name',$user->name) }}" class="mt-1 w-full rounded border px-3 py-2 dark:bg-gray-900 dark:border-gray-700">
        @error('name') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
      </div>
      <div>
        <label class="text-sm font-medium">Email (read-only)</label>
        <input value="{{ $user->email }}" disabled class="mt-1 w-full rounded border px-3 py-2 opacity-70 dark:bg-gray-900 dark:border-gray-700">
      </div>
    </div>

    <div class="rounded border p-4 dark:border-gray-700">
      <label class="inline-flex items-center gap-2">
        <input type="checkbox" name="is_buyer" value="1"
               @checked(old('is_buyer', property_exists($user,'is_buyer') ? (bool)$user->is_buyer : false))>
        <span>Buyer</span>
      </label>

      <div class="mt-4 grid gap-4 sm:grid-cols-2">
        <div>
          <label class="text-sm font-medium">Packs</label>
          <select name="pack_ids[]" multiple class="mt-1 w-full rounded border px-3 py-2 dark:bg-gray-900 dark:border-gray-700 min-h-[200px]">
            @foreach($packs as $p)
              <option value="{{ $p->id }}" @selected(in_array($p->id,$ownedPacks))>{{ $p->title }}</option>
            @endforeach
          </select>
        </div>
        <div>
          <label class="text-sm font-medium">Coaches</label>
          <select name="coach_ids[]" multiple class="mt-1 w-full rounded border px-3 py-2 dark:bg-gray-900 dark:border-gray-700 min-h-[200px]">
            @foreach($coaches as $c)
              <option value="{{ $c->id }}" @selected(in_array($c->id,$ownedCoaches))>{{ $c->name }}</option>
            @endforeach
          </select>
        </div>
      </div>
    </div>

    <div class="flex items-center gap-2">
      <button class="rounded bg-[var(--accent)] px-4 py-2 text-white">Save</button>
      <a href="{{ route('admin.users.show',$user) }}" class="rounded border px-4 py-2 dark:border-gray-700">Cancel</a>
    </div>
  </form>
</x-admin-layout>
