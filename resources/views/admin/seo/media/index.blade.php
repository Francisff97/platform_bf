<x-admin-layout title="SEO Media">
  <form method="GET" class="mb-4">
    <input name="search" value="{{ request('search') }}" placeholder="Search path..."
           class="rounded border p-2 dark:bg-gray-900 dark:border-gray-800">
    <button class="rounded bg-[var(--accent)] px-3 py-1.5 text-white">Search</button>
  </form>

  <form method="POST" action="{{ route('admin.seo.media.bulk') }}" class="rounded border dark:border-gray-800 overflow-hidden">
    @csrf
    <table class="w-full text-sm">
      <thead class="bg-gray-50 dark:bg-gray-800/60">
        <tr>
          <th class="px-3 py-2"><input type="checkbox" onclick="document.querySelectorAll('.rowcheck').forEach(c=>c.checked=this.checked)"></th>
          <th class="px-3 py-2 text-left">Preview</th>
          <th class="px-3 py-2 text-left">Path</th>
          <th class="px-3 py-2 text-left">Alt</th>
          <th class="px-3 py-2 text-left">Lazy</th>
          <th class="px-3 py-2"></th>
        </tr>
      </thead>
      <tbody>
        @forelse($assets as $a)
          <tr class="border-t dark:border-gray-800 align-top">
            <td class="px-3 py-2"><input class="rowcheck" type="checkbox" name="ids[]" value="{{ $a->id }}"></td>
            <td class="px-3 py-2">
              <img src="{{ $a->url() }}" class="h-14 w-14 object-cover rounded">
            </td>
            <td class="px-3 py-2">{{ $a->path }}</td>
            <td class="px-3 py-2">{{ $a->alt_text }}</td>
            <td class="px-3 py-2">{{ $a->is_lazy ? 'Yes' : 'No' }}</td>
            <td class="px-3 py-2 text-right">
              <a href="{{ route('admin.seo.media.edit',$a) }}" class="underline">Edit</a>
            </td>
          </tr>
        @empty
          <tr><td colspan="6" class="px-3 py-6 text-center text-gray-500">No items.</td></tr>
        @endforelse
      </tbody>
    </table>

    <div class="flex items-center gap-2 p-3 border-t dark:border-gray-800">
      <input type="text" name="alt_text" placeholder="Set ALT for selected..." class="rounded border p-2 dark:bg-gray-900 dark:border-gray-800">
      <label class="inline-flex items-center gap-2 text-sm">
        <input type="checkbox" name="is_lazy" value="1"> Lazy ON
      </label>
      <button class="ml-auto rounded bg-[var(--accent)] px-3 py-1.5 text-white">Apply to selected</button>
    </div>
  </form>

  <div class="mt-4">{{ $assets->links() }}</div>
</x-admin-layout>
