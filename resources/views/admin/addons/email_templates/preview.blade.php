<x-admin-layout title="Preview: {{ $tpl->name }}">
  <div class="mb-4 flex items-center justify-between">
    <h1 class="text-xl font-semibold">Preview: {{ $tpl->name }}</h1>
    <a class="rounded border px-4 py-2 text-sm hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800"
       href="{{ route('admin.addons.email-templates.edit',$tpl) }}">Edit</a>
  </div>

  <div class="mb-3 text-sm text-gray-500">
    Subject: <span class="font-medium">{{ $rendered['subject'] }}</span>
  </div>

  <div class="grid gap-6 sm:grid-cols-2">
    <div>
      <div class="mb-2 text-xs font-semibold uppercase text-gray-500">HTML</div>
      <iframe class="h-96 w-full rounded border dark:border-gray-800" srcdoc="{{ e($rendered['html']) }}"></iframe>
    </div>
    <div>
      <div class="mb-2 text-xs font-semibold uppercase text-gray-500">Plain text</div>
      <pre class="h-96 w-full overflow-auto rounded border bg-gray-50 p-3 dark:border-gray-800 dark:bg-gray-900">{{ $rendered['text'] }}</pre>
    </div>
  </div>
</x-admin-layout>