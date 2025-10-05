<x-admin-layout title="Analytics">
  @if (session('success'))
    <div class="mb-4 rounded border border-green-300 bg-green-50 px-3 py-2 text-sm text-green-800">
      {{ session('success') }}
    </div>
  @endif

  <div class="mb-4 rounded border border-gray-200 bg-gray-50 p-4 text-sm text-gray-700 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">
    Connect Google Tag Manager to your site. Paste your container ID (e.g. <code>GTM-XXXXXXX</code>).
  </div>

  <form method="POST" action="{{ route('admin.analytics.update') }}" class="max-w-xl grid gap-4">
    @csrf
    <div>
      <label class="block text-sm font-medium mb-1">GTM Container ID</label>
      <input type="text" name="gtm_container_id"
             value="{{ old('gtm_container_id', $s->gtm_container_id) }}"
             placeholder="GTM-XXXXXXX"
             class="w-full rounded border px-3 py-2 dark:bg-gray-900">
      @error('gtm_container_id')
        <div class="mt-1 text-xs text-red-600">{{ $message }}</div>
      @enderror
    </div>

    <div>
      <button class="rounded bg-[var(--accent)] px-4 py-2 text-white">Save</button>
      <a href="{{ route('admin.dashboard') }}" class="ml-3 underline">Cancel</a>
    </div>
  </form>
</x-admin-layout>