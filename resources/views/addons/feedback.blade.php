<x-admin-layout title="Customer Feedback">
  @php
    $items = \App\Models\DiscordMessage::where('kind','feedback')
              ->orderByDesc('message_created_at')->paginate(12);
  @endphp

  <h2 class="text-3xl font-bold mb-4">Customer Feedback</h2>
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
    @foreach($items as $m)
      <div class="rounded-xl border dark:border-gray-800 p-4 bg-white dark:bg-gray-900">
        <div class="flex items-center gap-2 text-sm mb-2">
          <div class="h-8 w-8 rounded-full overflow-hidden">
            <img src="https://ui-avatars.com/api/?name={{ urlencode($m->author_name) }}" class="h-8 w-8 object-cover">
          </div>
          <div class="font-medium">{{ $m->author_name }}</div>
          <div class="text-xs text-gray-500">in #{{ $m->channel_name }}</div>
        </div>
        <div class="text-sm leading-snug">{{ $m->content }}</div>
      </div>
    @endforeach
  </div>

  <div class="mt-6">{{ $items->links() }}</div>
</x-admin-layout>