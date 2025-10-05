<x-admin-layout title="Email templates">
  <div class="space-y-3">
    @foreach($items as $tpl)
      <div class="flex items-center justify-between rounded border p-3 dark:border-gray-800">
        <div>
          <div class="font-medium">{{ $tpl->key }}</div>
          <div class="text-sm text-gray-500">{{ $tpl->subject }}</div>
        </div>
        <a class="rounded bg-[var(--accent)] px-3 py-1.5 text-white" href="{{ route('admin.addons.email-templates.edit',$tpl) }}">Edit</a>
      </div>
    @endforeach
  </div>
</x-admin-layout>