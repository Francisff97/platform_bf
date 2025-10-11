<x-admin-layout title="Create user">
  <form method="POST" action="{{ route('admin.users.store') }}" class="grid gap-6">
    @csrf

    <div class="grid gap-4 sm:grid-cols-2">
      <div>
        <label class="text-sm font-medium">Name</label>
        <input name="name" value="{{ old('name') }}" class="mt-1 w-full rounded border px-3 py-2 dark:bg-gray-900 dark:border-gray-700">
        @error('name') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
      </div>
      <div>
        <label class="text-sm font-medium">Email</label>
        <input name="email" type="email" value="{{ old('email') }}" class="mt-1 w-full rounded border px-3 py-2 dark:bg-gray-900 dark:border-gray-700">
        @error('email') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
      </div>
      <div class="sm:col-span-2">
        <label class="text-sm font-medium">Password (optional)</label>
        <input name="password" type="password" class="mt-1 w-full rounded border px-3 py-2 dark:bg-gray-900 dark:border-gray-700" placeholder="Leave empty to auto-generate">
        @error('password') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
      </div>
    </div>

    <div class="rounded border p-4 dark:border-gray-700">
      <label class="inline-flex items-center gap-2">
        <input type="checkbox" name="is_buyer" value="1" @checked(old('is_buyer'))>
        <span>Buyer</span>
      </label>

      <div class="mt-4 grid gap-4 sm:grid-cols-2">
        <div>
          <label class="text-sm font-medium">Packs</label>
          <select name="pack_ids[]" multiple class="mt-1 w-full rounded border px-3 py-2 dark:bg-gray-900 dark:border-gray-700 min-h-[180px]">
            @foreach($packs as $p)
              <option value="{{ $p->id }}">{{ $p->title }}</option>
            @endforeach
          </select>
        </div>
        <div>
          <label class="text-sm font-medium">Coaches</label>
          <select name="coach_ids[]" multiple class="mt-1 w-full rounded border px-3 py-2 dark:bg-gray-900 dark:border-gray-700 min-h-[180px]">
            @foreach($coaches as $c)
              <option value="{{ $c->id }}">{{ $c->name }}</option>
            @endforeach
          </select>
        </div>
      </div>
    </div>

    <div class="flex items-center gap-2">
      <button class="rounded bg-[var(--accent)] px-4 py-2 text-white">Create</button>
      <a href="{{ route('admin.users.index') }}" class="rounded border px-4 py-2 dark:border-gray-700">Cancel</a>
    </div>
  </form>
</x-admin-layout>
