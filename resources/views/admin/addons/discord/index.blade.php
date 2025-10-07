<x-admin-layout title="Discord add-ons">
  <p class="mb-6 text-sm text-gray-600 dark:text-gray-300">
    Inserisci gli ID di Discord: server e canali che il bot deve leggere.
  </p>

  @if (session('success'))
    <div class="mb-4 rounded-lg border border-emerald-300 bg-emerald-50 px-3 py-2 text-sm text-emerald-800">
      {{ session('success') }}
    </div>
  @endif

  <form method="POST" action="{{ route('admin.addons.discord.save') }}" class="grid gap-6">
    @csrf

    <div class="grid gap-6">
      {{-- Server ID --}}
      <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <label class="flex items-center justify-between">
          <span class="text-sm font-medium text-gray-900 dark:text-gray-100">Discord Server ID</span>
          @if($s?->discord_server_id)
            <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs text-gray-700 dark:bg-gray-800 dark:text-gray-300">
              attuale: {{ Str::limit($s->discord_server_id, 6, '…') }}
            </span>
          @endif
        </label>
        <div class="mt-2 flex items-center gap-2">
          <input
            name="discord_server_id"
            value="{{ old('discord_server_id', $s->discord_server_id ?? '') }}"
            placeholder="es. 843250070832545852"
            inputmode="numeric"
            pattern="[0-9]*"
            class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 font-mono text-sm tracking-wide placeholder:text-gray-400 focus:border-[var(--accent)] focus:outline-none focus:ring-2 focus:ring-[var(--accent)]/20 dark:border-gray-700 dark:bg-gray-950"
          />
          <button
            type="button"
            x-data
            @click="navigator.clipboard.writeText($el.previousElementSibling.value)"
            class="rounded-lg border px-3 py-2 text-sm hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800"
            title="Copia valore"
          >Copy</button>
        </div>
        <p class="mt-1 text-xs text-gray-500">ID “snowflake” del server (guild).</p>
        @error('discord_server_id')
          <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
        @enderror
      </div>

      {{-- Announcements channel --}}
      <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <label class="flex items-center justify-between">
          <span class="text-sm font-medium text-gray-900 dark:text-gray-100">Announcements channel ID</span>
          @if($s?->discord_announcements_channel_id)
            <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs text-gray-700 dark:bg-gray-800 dark:text-gray-300">
              attuale: {{ Str::limit($s->discord_announcements_channel_id, 6, '…') }}
            </span>
          @endif
        </label>
        <div class="mt-2 flex items-center gap-2">
          <input
            name="discord_announcements_channel_id"
            value="{{ old('discord_announcements_channel_id', $s->discord_announcements_channel_id ?? '') }}"
            placeholder="es. 843250070832545857"
            inputmode="numeric"
            pattern="[0-9]*"
            class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 font-mono text-sm tracking-wide placeholder:text-gray-400 focus:border-[var(--accent)] focus:outline-none focus:ring-2 focus:ring-[var(--accent)]/20 dark:border-gray-700 dark:bg-gray-950"
          />
          <button
            type="button"
            x-data
            @click="navigator.clipboard.writeText($el.previousElementSibling.value)"
            class="rounded-lg border px-3 py-2 text-sm hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800"
            title="Copia valore"
          >Copy</button>
        </div>
        <div class="mt-1 text-xs text-gray-500">
          Ultimi post importati: <span class="font-medium">{{ $annCount }}</span>
        </div>
        @error('discord_announcements_channel_id')
          <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
        @enderror
      </div>

      {{-- Feedback channel --}}
      <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <label class="flex items-center justify-between">
          <span class="text-sm font-medium text-gray-900 dark:text-gray-100">Feedback channel ID</span>
          @if($s?->discord_feedback_channel_id)
            <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs text-gray-700 dark:bg-gray-800 dark:text-gray-300">
              attuale: {{ Str::limit($s->discord_feedback_channel_id, 6, '…') }}
            </span>
          @endif
        </label>
        <div class="mt-2 flex items-center gap-2">
          <input
            name="discord_feedback_channel_id"
            value="{{ old('discord_feedback_channel_id', $s->discord_feedback_channel_id ?? '') }}"
            placeholder="es. 1062155869892116622"
            inputmode="numeric"
            pattern="[0-9]*"
            class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 font-mono text-sm tracking-wide placeholder:text-gray-400 focus:border-[var(--accent)] focus:outline-none focus:ring-2 focus:ring-[var(--accent)]/20 dark:border-gray-700 dark:bg-gray-950"
          />
          <button
            type="button"
            x-data
            @click="navigator.clipboard.writeText($el.previousElementSibling.value)"
            class="rounded-lg border px-3 py-2 text-sm hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800"
            title="Copia valore"
          >Copy</button>
        </div>
        <div class="mt-1 text-xs text-gray-500">
          Ultimi post importati: <span class="font-medium">{{ $fbkCount }}</span>
        </div>
        @error('discord_feedback_channel_id')
          <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
        @enderror
      </div>
    </div>

    <div class="flex items-center gap-2">
      <button class="rounded-lg bg-[var(--accent)] px-4 py-2 text-white shadow hover:opacity-90">Save</button>
      <a href="{{ route('admin.addons.discord.sync') }}"
         class="rounded-lg border px-4 py-2 shadow-sm hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800">Sync now</a>
    </div>
  </form>
</x-admin-layout>
