<x-app-layout>
  <x-slot name="header">
    <div class="flex items-center justify-between">
      <h2 class="text-2xl font-semibold leading-tight">Profile</h2>
      {{-- opzionale: mini hint --}}
      <span class="rounded-full bg-white/40 px-2 py-0.5 text-xs backdrop-blur ring-1 ring-black/10 dark:bg-white/10 dark:ring-white/10">
        Manage account & security
      </span>
    </div>
  </x-slot>

  <div class="py-8">
    <div class="mx-auto grid max-w-5xl gap-6 px-3 sm:px-6 lg:grid-cols-2">
      {{-- Profile info --}}
      <section class="rounded-2xl border bg-white/70 p-5 shadow-sm backdrop-blur ring-1 ring-black/5 dark:border-gray-800 dark:bg-gray-900/70 dark:ring-white/10">
        @include('profile.partials.update-profile-information-form')
      </section>

      {{-- Update password --}}
      <section class="rounded-2xl border bg-white/70 p-5 shadow-sm backdrop-blur ring-1 ring-black/5 dark:border-gray-800 dark:bg-gray-900/70 dark:ring-white/10">
        @include('profile.partials.update-password-form')
      </section>

      {{-- Danger zone – full width on desktop --}}
      <section class="rounded-2xl border bg-white/70 p-5 shadow-sm backdrop-blur ring-1 ring-black/5 dark:border-gray-800 dark:bg-gray-900/70 dark:ring-white/10 lg:col-span-2">
        @include('profile.partials.delete-user-form')
      </section>
    </div>
  </div>
</x-app-layout>