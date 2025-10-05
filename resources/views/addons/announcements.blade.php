<x-admin-layout title="Announcements">
  @php
    $items = \App\Models\DiscordMessage::where('kind','announcement')
              ->orderByDesc('message_created_at')->paginate(12);
  @endphp

  <h2 class="text-3xl font-bold mb-4">Announcements</h2>
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
    @foreach($items as $m)
      <div class="rounded-xl bg-gray-900 text-gray-100 p-3">
        @if(!empty($m->attachments[0]['url']))
          <img src="{{ $m->attachments[0]['url'] }}" class="w-full h-36 object-cover rounded-lg mb-2">
        @endif
        <div class="text-xs text-gray-400 mb-1">
          {{ $m->author_name }} • {{ $m->channel_name }}
        </div>
        <div class="text-sm leading-snug line-clamp-4">{{ $m->content }}</div>
      </div>
    @endforeach
  </div>

  <div class="mt-6">{{ $items->links() }}</div>
</x-admin-layout>