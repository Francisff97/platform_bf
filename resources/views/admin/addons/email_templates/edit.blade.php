<x-admin-layout title="Edit template">
  <h1 class="mb-4 text-xl font-semibold">Edit: {{ $tpl->name }}</h1>

  @if($errors->any())
    <div class="mb-4 rounded border border-red-300 bg-red-50 px-3 py-2 text-sm text-red-800">
      Fix the errors below.
    </div>
  @endif

  <form method="POST" action="{{ route('admin.addons.email-templates.update',$tpl) }}" class="grid gap-6">
    @csrf
    @method('PUT')

    <div class="grid gap-4 sm:grid-cols-2">
      <div>
        <label class="text-sm font-medium">Name</label>
        <input type="text" name="name" value="{{ old('name',$tpl->name) }}"
               class="mt-1 w-full rounded border px-3 py-2 dark:bg-gray-900 dark:border-gray-700">
        @error('name')
          <div class="mt-1 text-xs text-red-600">{{ $message }}</div>
        @enderror
      </div>

      <div>
        <label class="text-sm font-medium">Subject</label>
        <input type="text" name="subject" value="{{ old('subject',$tpl->subject) }}"
               class="mt-1 w-full rounded border px-3 py-2 dark:bg-gray-900 dark:border-gray-700">
        @error('subject')
          <div class="mt-1 text-xs text-red-600">{{ $message }}</div>
        @enderror
      </div>
    </div>

    <div>
      <label class="text-sm font-medium">HTML body (placeholders tipo {{ '{' }}{ customer_name }})</label>
      <textarea name="html_body" rows="10"
                class="mt-1 w-full rounded border px-3 py-2 dark:bg-gray-900 dark:border-gray-700">{{ old('html_body',$tpl->html_body) }}</textarea>
      @error('html_body')
        <div class="mt-1 text-xs text-red-600">{{ $message }}</div>
      @enderror
    </div>

    <div>
      <label class="text-sm font-medium">Text body (fallback)</label>
      <textarea name="text_body" rows="6"
                class="mt-1 w-full rounded border px-3 py-2 dark:bg-gray-900 dark:border-gray-700">{{ old('text_body',$tpl->text_body) }}</textarea>
      @error('text_body')
        <div class="mt-1 text-xs text-red-600">{{ $message }}</div>
      @enderror
    </div>

    <label class="inline-flex items-center gap-2">
      <input type="checkbox" name="enabled" value="1" @checked(old('enabled',$tpl->enabled))>
      <span>Enabled</span>
    </label>

    <div class="flex items-center gap-2">
      <button class="rounded bg-[var(--accent)] px-4 py-2 text-white hover:opacity-90">Save</button>
      <a class="rounded border px-4 py-2 hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800"
         href="{{ route('admin.addons.email-templates') }}">Back</a>
      <a class="rounded border px-4 py-2 hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800"
         href="{{ route('admin.addons.email-templates.preview',$tpl) }}">Preview</a>
    </div>
  </form>

  <form method="POST" action="{{ route('admin.addons.email-templates.send-test',$tpl) }}" class="mt-8 flex flex-wrap items-end gap-3">
    @csrf
    <div class="w-full sm:w-auto">
      <label class="text-sm font-medium">Send test to</label>
      <input type="email" name="to" placeholder="you@example.com"
             class="mt-1 w-full rounded border px-3 py-2 dark:bg-gray-900 dark:border-gray-700">
    </div>
    <button class="rounded border px-4 py-2 hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800">
      Send test
    </button>
  </form>
</x-admin-layout>