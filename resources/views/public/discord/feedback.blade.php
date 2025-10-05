<x-app-layout>
  <x-slot name="header"><h1 class="text-2xl font-bold">Feedback</h1></x-slot>
  {{-- copia lo stesso markup di news --}}
  @include('public.discord.partials.list', ['posts' => $posts])
</x-app-layout>