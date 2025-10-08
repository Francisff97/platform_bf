@props(['posts'])

{{-- Wrapper: slider su mobile, grid su desktop --}}
<div class="md:grid md:grid-cols-2 lg:grid-cols-3 gap-4 md:gap-6
            overflow-x-auto md:overflow-visible snap-x snap-mandatory no-scrollbar flex md:block gap-4 pb-2">
  @foreach($posts as $p)
    <article
      class="min-w-[85%] sm:min-w-[70%] md:min-w-0 snap-start
             rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900
             shadow-sm hover:shadow-md transition-shadow p-4">
      <div class="flex items-start gap-3">
        @if($p->author_avatar)
          <img src="{{ $p->author_avatar }}" class="h-10 w-10 rounded-full object-cover" alt="{{ $p->author_name }}" />
        @else
          <div class="h-10 w-10 rounded-full bg-gray-200 dark:bg-gray-700"></div>
        @endif

        <div class="min-w-0">
          <div class="flex items-center gap-2">
            <div class="truncate font-medium">{{ $p->author_name ?: 'Unknown' }}</div>
            <div class="text-xs text-gray-500">· {{ optional($p->posted_at)->diffForHumans() }}</div>
          </div>
          <div class="mt-1 whitespace-pre-wrap text-sm leading-relaxed">
            {{ $p->content }}
          </div>

          @if(!empty($p->attachments))
            <div class="mt-3 flex flex-wrap gap-2">
              @foreach($p->attachments as $a)
                @php $url = is_array($a) ? ($a['url'] ?? '#') : (string)$a; @endphp
                <a href="{{ $url }}" target="_blank"
                   class="inline-flex items-center text-xs rounded px-2 py-1 border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800">
                  Attachment
                </a>
              @endforeach
            </div>
          @endif
        </div>
      </div>
    </article>
  @endforeach
</div>

{{-- Pagination desktop --}}
<div class="mt-6 hidden md:block">
  {{ $posts->links() }}
</div>

{{-- Mobile hint --}}
<div class="mt-3 text-center text-xs text-gray-500 md:hidden">Swipe sideways for more →</div>

<style>
  .no-scrollbar::-webkit-scrollbar { display: none; }
  .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>
