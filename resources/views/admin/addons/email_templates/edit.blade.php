<x-admin-layout title="Edit template">
  <h1 class="mb-4 text-xl font-semibold">Edit: {{ $tpl->name }}</h1>

  @if($errors->any())
    <div class="mb-4 rounded border border-red-300 bg-red-50 px-3 py-2 text-sm text-red-800 dark:border-red-500/40 dark:bg-red-900/20 dark:text-red-100">
      Fix the errors below.
    </div>
  @endif

  <form method="POST"
        action="{{ route('admin.addons.email-templates.update',$tpl) }}"
        class="mx-auto grid max-w-4xl gap-5 rounded-2xl border border-[color:var(--accent)]/30 bg-white/70 p-6 shadow-sm backdrop-blur
               dark:border-[color:var(--accent)]/30 dark:bg-gray-900/70">
    @csrf
    @method('PUT')

    <div class="grid gap-4 sm:grid-cols-2">
      <label class="block">
        <div class="mb-1 text-sm font-medium">Name</div>
        <input type="text" name="name" value="{{ old('name',$tpl->name) }}"
               class="w-full rounded-xl border border-[color:var(--accent)]/40 px-3 py-2 focus:ring-2 focus:ring-[color:var(--accent)]
                      dark:bg-black/70 dark:text-white dark:border-gray-800">
        @error('name') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
      </label>

      <label class="block">
        <div class="mb-1 text-sm font-medium">Subject</div>
        <input type="text" name="subject" value="{{ old('subject',$tpl->subject) }}"
               class="w-full rounded-xl border border-[color:var(--accent)]/40 px-3 py-2 focus:ring-2 focus:ring-[color:var(--accent)]
                      dark:bg-black/70 dark:text-white dark:border-gray-800">
        @error('subject') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
      </label>
    </div>

    <label class="block">
      <div class="mb-1 text-sm font-medium">HTML body</div>
      <textarea name="html_body" rows="10"
                class="w-full rounded-xl border border-[color:var(--accent)]/40 px-3 py-2 focus:ring-2 focus:ring-[color:var(--accent)]
                       dark:bg-black/70 dark:text-white dark:border-gray-800">{{ old('html_body',$tpl->html_body) }}</textarea>
      <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
        You can use placeholders like <code>{{ '{' }}{ customer_name }}</code>, <code>{{ '{' }}{ order_number }}</code>, <code>{{ '{' }}{ pack_title }}</code>.
      </p>
      @error('html_body') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
    </label>

    <label class="block">
      <div class="mb-1 text-sm font-medium">Text body (fallback)</div>
      <textarea name="text_body" rows="6"
                class="w-full rounded-xl border border-[color:var(--accent)]/40 px-3 py-2 focus:ring-2 focus:ring-[color:var(--accent)]
                       dark:bg-black/70 dark:text-white dark:border-gray-800">{{ old('text_body',$tpl->text_body) }}</textarea>
      @error('text_body') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
    </label>

    <label class="inline-flex items-center gap-2">
      <input type="checkbox" name="enabled" value="1" @checked(old('enabled',$tpl->enabled))>
      <span>Enabled</span>
    </label>

    <div class="flex items-center gap-2">
      <button class="rounded-xl bg-[var(--accent)] px-4 py-2 text-white hover:opacity-90">Save</button>
      <a class="rounded border px-4 py-2 hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800"
         href="{{ route('admin.addons.email-templates') }}">Back</a>
      <a class="rounded border px-4 py-2 hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800"
         href="{{ route('admin.addons.email-templates.preview',$tpl) }}">Preview</a>
    </div>
  </form>

  <form method="POST" action="{{ route('admin.addons.email-templates.send-test',$tpl) }}"
        class="mx-auto mt-6 flex max-w-4xl flex-wrap items-end gap-3 rounded-2xl border border-[color:var(--accent)]/30 bg-white/50 p-4 dark:border-gray-800 dark:bg-gray-900/40">
    @csrf
    <div class="w-full sm:w-auto">
      <div class="mb-1 text-sm font-medium">Send test to</div>
      <input type="email" name="to" placeholder="you@example.com"
             class="w-full rounded-xl border border-[color:var(--accent)]/40 px-3 py-2 focus:ring-2 focus:ring-[color:var(--accent)]
                    dark:bg-black/70 dark:text-white dark:border-gray-800">
    </div>
    <button class="rounded border px-4 py-2 hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800">
      Send test
    </button>
  </form>
</x-admin-layout>
