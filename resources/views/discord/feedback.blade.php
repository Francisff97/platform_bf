<x-app-layout title="Customer Feedback">
  <section class="mx-auto max-w-6xl px-4 py-8">
    <h1 class="text-4xl font-black tracking-tight mb-1">Customer Feedback</h1>
    <p class="text-sm text-gray-500 mb-6">What customers tell about us.</p>

    <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
      @forelse($items as $m)
        <article class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm dark:bg-gray-900 dark:border-gray-800">
          <div class="flex items-center gap-3 mb-2">
            <img src="https://www.gravatar.com/avatar/{{ md5(strtolower(trim($m->author_id.'@discord'))) }}?s=64&d=identicon"
                 class="h-9 w-9 rounded-full" alt="">
            <div class="min-w-0">
              <div class="text-sm font-semibold">{{ $m->author_name ?? 'Discord user' }}</div>
              <div class="text-xs text-gray-500">{{ optional($m->posted_at)->diffForHumans() }}</div>
            </div>
          </div>
          <p class="text-sm text-gray-700 dark:text-gray-300">{{ $m->content }}</p>
        </article>
      @empty
        <div class="col-span-full text-sm text-gray-500">No feedback yet.</div>
      @endforelse
    </div>

    <div class="mt-6">{{ $items->links() }}</div>
  </section>
</x-app-layout>
