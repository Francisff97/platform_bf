<x-app-layout>
  <x-slot name="header"><h1 class="text-2xl font-bold">Announcements</h1></x-slot>
  @include('public.discord.partials.list', ['posts' => $posts])
</x-app-layout>
