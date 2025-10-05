<x-app-layout>
  <x-slot name="header"><h1 class="text-2xl font-bold">News</h1></x-slot>

  <div class="space-y-4">
    @forelse($posts as $p)
      <article class="rounded border p-4 dark:border-gray-800">
        <div class="mb-1 text-xs text-gray-500">{{ $p->posted_at?->format('Y-m-d H:i') }}</div>
        <div class="flex items-start gap-3">
          @if($p->author_avatar)
            <img src="{{ $p->author_avatar }}" class="h-8 w-8 rounded-full" />
          @endif
          <div>
            <div class="text-sm font-semibold">{{ $p->author_name }}</div>
            <div class="whitespace-pre-line text-sm">{{ $p->content }}</div>
            @if(!empty($p->attachments))
              <div class="mt-2 flex flex-wrap gap-2">
                @foreach($p->attachments as $a)
                  <a href="{{ $a['url'] ?? '#' }}" target="_blank" class="text-xs underline">Attachment</a>
                @endforeach
              </div>
            @endif
          </div>
        </div>
      </article>
    @empty
      <div class="text-sm text-gray-500">No posts yet.</div>
    @endforelse

    {{ $posts->links() }}
  </div>
</x-app-layout>