<x-admin-layout title="Email templates">
  <div class="mb-4 flex items-center justify-between">
    <h1 class="text-xl font-semibold">Email templates</h1>
  </div>

  @if (session('success'))
    <div class="rounded border border-green-300 bg-green-50 px-3 py-2 text-sm text-green-800">
      {{ session('success') }}
    </div>
  @endif

  <div class="divide-y rounded-lg border dark:border-gray-800">
    @forelse($templates as $t)
      <div class="flex items-center justify-between p-4">
        <div>
          <div class="font-medium">{{ $t->name }}</div>
          <div class="text-xs text-gray-500">slug: {{ $t->slug }}</div>
          <div class="text-xs text-gray-500">status: {{ $t->enabled ? 'enabled' : 'disabled' }}</div>
        </div>
        <div class="flex items-center gap-2">
          <a class="rounded border px-3 py-1 text-sm hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800"
             href="{{ route('admin.addons.email-templates.preview',$t) }}">Preview</a>
          <a class="rounded bg-[var(--accent)] px-3 py-1 text-sm font-medium text-white hover:opacity-90"
             href="{{ route('admin.addons.email-templates.edit',$t) }}">Edit</a>
        </div>
      </div>
    @empty
      <div class="p-6 text-sm text-gray-500">No templates yet.</div>
    @endforelse
  </div>
</x-admin-layout>