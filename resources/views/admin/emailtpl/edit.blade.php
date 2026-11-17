<x-admin-layout :title="'Edit template: '.$template->key">
  @if (session('success'))
    <div class="mb-4 rounded border border-green-300 bg-green-50 px-3 py-2 text-sm text-green-800">{{ session('success') }}</div>
  @endif

  <form method="POST" action="{{ route('admin.addons.email-templates.update', $template) }}" class="space-y-4">
    @csrf @method('PUT')

    <label class="block">
      <div class="text-sm text-gray-600 mb-1">Subject</div>
      <input name="subject" class="w-full rounded border px-3 py-2 dark:border-gray-800"
             value="{{ old('subject',$template->subject) }}">
    </label>

    <label class="block">
      <div class="text-sm text-gray-600 mb-1">Body (Blade enabled)</div>
      <textarea name="body_html" rows="14" class="w-full rounded border px-3 py-2 font-mono text-sm dark:border-gray-800">{{ old('body_html',$template->body_html) }}</textarea>
    </label>

    <label class="inline-flex items-center gap-2">
      <input type="checkbox" name="enabled" value="1" {{ $template->enabled ? 'checked' : '' }}>
      <span>Enabled</span>
    </label>

    <button class="rounded bg-[var(--accent)] px-4 py-2 text-white">Save</button>
  </form>

  <div class="mt-8 text-sm text-gray-500">
    <p>Available variables:</p>
    <ul class="list-disc pl-6">
      <li><code>$order</code> (model instance)</li>
      <li><code>$customer_name</code></li>
    </ul>
  </div>
</x-admin-layout>