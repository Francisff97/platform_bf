<x-app-layout title="Customer Feedback">
  <section class="mx-auto max-w-6xl px-4 py-8">
    <header class="mb-6">
      <h1 class="text-4xl font-black tracking-tight">Customer Feedback</h1>
      <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">What customers tell about us.</p>
    </header>

    <div
      class="flex gap-4 overflow-x-auto snap-x snap-mandatory no-scrollbar pb-2
             md:grid md:grid-cols-2 lg:grid-cols-3 md:gap-6 md:overflow-visible md:snap-none">

      @forelse($items as $m)
        @php
          $avatar = $m->author_avatar
            ?? ('https://www.gravatar.com/avatar/'.md5(strtolower(trim(($m->author_id ?? 'user').'@discord'))).'?s=128&d=identicon');
        @endphp

        <article
          class="snap-start min-w-[85%] sm:min-w-[70%] md:min-w-0
                 rounded-2xl border border-gray-200 dark:border-gray-800
                 bg-white dark:bg-gray-900 shadow-sm hover:shadow-md transition-shadow p-4">
          <div class="flex items-start gap-3">
            <img src="{{ $avatar }}" class="h-10 w-10 rounded-full object-cover" alt="Author">
            <div class="min-w-0">
              <div class="flex items-center gap-2">
                <div class="truncate text-sm font-semibold text-gray-900 dark:text-gray-100">
                  {{ $m->author_name ?? 'Discord user' }}
                </div>
                <span class="text-xs text-gray-500">· {{ optional($m->posted_at)->diffForHumans() }}</span>
              </div>
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
          No feedback yet.
        </div>
      @endforelse
    </div>

    <div class="mt-2 text-center text-xs text-gray-500 md:hidden">Swipe sideways for more →</div>

    <div class="mt-6 hidden md:block">
      {{ $items->links() }}
    </div>
  </section>

  <style>
    .no-scrollbar::-webkit-scrollbar { display: none; }
    .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
  </style>
</x-app-layout>
