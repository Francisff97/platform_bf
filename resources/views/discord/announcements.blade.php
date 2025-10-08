<x-app-layout title="Announcements">
  <section class="mx-auto max-w-6xl px-4 py-8">
    <header class="mb-6">
      <h1 class="text-4xl font-black tracking-tight">Announcements</h1>
      <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Latest announcements and news.</p>
    </header>

    {{-- Slider su mobile, grid su md+ --}}
    <div
      class="flex gap-4 overflow-x-auto snap-x snap-mandatory no-scrollbar pb-2
             md:grid md:grid-cols-2 lg:grid-cols-3 md:gap-6 md:overflow-visible md:snap-none">

      @forelse($items as $m)
        @php
          $img = collect($m->attachments)->first()['url'] ?? null;
          $avatar = $m->author_avatar
            ?? ('https://www.gravatar.com/avatar/'.md5(strtolower(trim(($m->author_id ?? 'user').'@discord'))).'?s=128&d=identicon');
        @endphp

        <article
          class="snap-start min-w-[85%] sm:min-w-[70%] md:min-w-0
                 rounded-2xl border border-gray-200/60 dark:border-gray-800
                 bg-white/80 dark:bg-gray-900/80 backdrop-blur
                 shadow-sm hover:shadow-md transition-shadow">
          @if($img)
            <div class="relative">
              <img src="{{ $img }}" alt="" class="h-48 w-full rounded-t-2xl object-cover">
              <div class="absolute inset-0 rounded-t-2xl ring-1 ring-black/5"></div>
            </div>
          @endif

          <div class="p-4">
            <div class="flex items-center gap-3">
              <img src="{{ $avatar }}" class="h-9 w-9 rounded-full object-cover" alt="Author">
              <div class="min-w-0">
                <div class="truncate text-sm font-semibold text-gray-900 dark:text-gray-100">
                  {{ $m->author_name ?? 'Discord user' }}
                </div>
                <div class="text-xs text-gray-500">
                  {{ optional($m->posted_at)->diffForHumans() }}
                </div>
              </div>
            </div>

            <div class="mt-3">
              <h3 class="text-xs uppercase tracking-wide text-[var(--accent)]/90 dark:text-[var(--accent)]/90">
                {{ $m->channel_name ?? 'Announcement' }}
              </h3>
              <p class="mt-1 text-sm leading-relaxed text-gray-700 dark:text-gray-300 whitespace-pre-wrap">
                {{ $m->content }}
              </p>

              @if(!empty($m->attachments))
                <div class="mt-3 flex flex-wrap gap-2">
                  @foreach($m->attachments as $a)
                    @php $url = is_array($a) ? ($a['url'] ?? '#') : (string)$a; @endphp
                    <a href="{{ $url }}" target="_blank"
                       class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs
                              border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800">
                      Attachment
                    </a>
                  @endforeach
                </div>
              @endif
            </div>
          </div>
        </article>
      @empty
        <div class="snap-start min-w-[85%] md:min-w-0 col-span-full text-sm text-gray-500">
          No announcements yet.
        </div>
      @endforelse
    </div>

    {{-- Hint mobile swipe --}}
    <div class="mt-2 text-center text-xs text-gray-500 md:hidden">Swipe sideways for more →</div>

    {{-- Pagination desktop --}}
    <div class="mt-6 hidden md:block">
      {{ $items->links() }}
    </div>
  </section>

  <style>
    .no-scrollbar::-webkit-scrollbar { display: none; }
    .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
  </style>
</x-app-layout>
