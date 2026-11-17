<x-admin-layout title="Add-ons">
  @if (session('success'))
    <div class="mb-4 rounded border border-green-300 bg-green-50 px-3 py-2 text-sm text-green-800">
      {{ session('success') }}
    </div>
  @endif

  <form method="POST" action="{{ route('admin.addons.update') }}" class="grid max-w-xl gap-4">
    @csrf

    <label class="flex items-center gap-3">
      <input type="checkbox" name="addons" {{ !empty($features['addons']) ? 'checked' : '' }}>
      <span>Addons (master)</span>
    </label>

    <label class="flex items-center gap-3">
      <input type="checkbox" name="email_templates" {{ !empty($features['email_templates']) ? 'checked' : '' }}>
      <span>Email templates</span>
    </label>

    <label class="flex items-center gap-3">
      <input type="checkbox" name="discord_integration" {{ !empty($features['discord_integration']) ? 'checked' : '' }}>
      <span>Discord integration</span>
    </label>

    <label class="flex items-center gap-3">
      <input type="checkbox" name="tutorials" {{ !empty($features['tutorials']) ? 'checked' : '' }}>
      <span>Tutorials</span>
    </label>

    <label class="flex items-center gap-3">
      <input type="checkbox" name="announcements" {{ !empty($features['announcements']) ? 'checked' : '' }}>
      <span>Announcements</span>
    </label>

    <button class="rounded bg-[var(--accent)] px-4 py-2 text-white">Save</button>
  </form>
</x-admin-layout>