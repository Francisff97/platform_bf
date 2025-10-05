<x-admin-layout title="Discord add-ons">
  <p class="mb-4 text-sm text-gray-600 dark:text-gray-300">
    Configure the Discord channels to read from. The pages will be visible only if enabled.
  </p>

  @if (session('success'))
    <div class="mb-4 rounded border border-green-300 bg-green-50 px-3 py-2 text-sm text-green-800">
      {{ session('success') }}
    </div>
  @endif

  <form method="POST" action="{{ route('admin.addons.discord.save') }}" class="grid gap-6">
    @csrf
    <div class="grid gap-4 sm:grid-cols-2">
      <div>
        <label class="text-sm font-medium">Discord Server ID</label>
        <input name="discord_server_id" value="{{ old('discord_server_id',$s->discord_server_id ?? '') }}"
               class="mt-1 w-full rounded border px-3 py-2 dark:bg-gray-900 dark:border-gray-700" />
      </div>
      <div></div>
      <div>
        <label class="text-sm font-medium">Announcements channel ID</label>
        <input name="discord_announcements_channel_id" value="{{ old('discord_announcements_channel_id',$s->discord_announcements_channel_id ?? '') }}"
               class="mt-1 w-full rounded border px-3 py-2 dark:bg-gray-900 dark:border-gray-700" />
        <div class="mt-1 text-xs text-gray-500">Fetched posts: {{ $annCount }}</div>
      </div>
      <div class="flex items-center gap-2 mt-6">
        <input type="hidden" name="discord_news_enabled" value="0">
        <input type="checkbox" name="discord_news_enabled" value="1"
               @checked(old('discord_news_enabled', $s->discord_news_enabled ?? false))>
        <span>Enable /news page</span>
      </div>

      <div>
        <label class="text-sm font-medium">Feedback channel ID</label>
        <input name="discord_feedback_channel_id" value="{{ old('discord_feedback_channel_id',$s->discord_feedback_channel_id ?? '') }}"
               class="mt-1 w-full rounded border px-3 py-2 dark:bg-gray-900 dark:border-gray-700" />
        <div class="mt-1 text-xs text-gray-500">Fetched posts: {{ $fbkCount }}</div>
      </div>
      <div class="flex items-center gap-2 mt-6">
        <input type="hidden" name="discord_feedback_enabled" value="0">
        <input type="checkbox" name="discord_feedback_enabled" value="1"
               @checked(old('discord_feedback_enabled', $s->discord_feedback_enabled ?? false))>
        <span>Enable /feedback page</span>
      </div>
    </div>

    <div class="flex items-center gap-2">
      <button class="rounded bg-[var(--accent)] px-4 py-2 text-white hover:opacity-90">Save</button>
      <a href="{{ route('admin.addons.discord.sync') }}"
         class="rounded border px-4 py-2 hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800">Sync now</a>
    </div>
  </form>
</x-admin-layout>