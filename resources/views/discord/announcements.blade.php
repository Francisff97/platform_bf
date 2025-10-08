<x-app-layout title="Announcements">
  <section class="mx-auto max-w-6xl px-4 py-8">
    <h1 class="text-4xl font-black tracking-tight mb-1">Announcements</h1>
    <p class="text-sm text-gray-500 mb-6">Latest announcements and news.</p>

    <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
      @forelse($items as $m)
        <article class="rounded-2xl bg-gray-900/90 text-white p-3 shadow-lg">
          @php
            $img = collect($m->attachments)->first()['url'] ?? null;
          @endphp
          @if($img)
            <img src="{{ $img }}" alt="" class="mb-3 h-40 w-full rounded-xl object-cover" />
          @endif

          <h3 class="text-sm font-semibold mb-1">{{ $m->channel_name ?? 'Announcement' }}</h3>
          <p class="line-clamp-3 text-sm/5 text-gray-300">{{ $m->content }}</p>

          <div class="mt-3 flex items-center justify-between text-[11px] text-gray-400">
            <span>{{ $m->author_name ?? 'User' }}</span>
            <time datetime="{{ $m->posted_at?->toAtomString() }}">{{ optional($m->posted_at)->diffForHumans() }}</time>
          </div>
        </article>
      @empty
        <div class="col-span-full text-sm text-gray-500">No announcements yet.</div>
      @endforelse
    </div>

    <div class="mt-6">{{ $items->links() }}</div>
  </section>
</x-app-layout>
