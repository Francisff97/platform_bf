{{-- Recent purchases widget --}}
<div class="rounded-2xl border border-[color:var(--accent)]/30 bg-white/80 p-4 shadow-sm backdrop-blur
            dark:border-[color:var(--accent)]/25 dark:bg-gray-900/60">
  <div class="mb-3 flex items-center justify-between">
    <h3 class="text-sm font-semibold">Recent purchases</h3>
    <a href="{{ route('admin.orders.index') }}" class="text-xs opacity-70 hover:opacity-100">View all</a>
  </div>

  @if(empty($recentPurchases))
    <div class="grid gap-2">
      <div class="rounded-xl border border-dashed px-3 py-6 text-center text-sm opacity-70 dark:border-gray-800">
        No purchases yet.
      </div>
    </div>
  @else
    <ul class="grid gap-3">
      @foreach($recentPurchases as $r)
        @php
          $badge = $r->type === 'pack'
            ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-200'
            : ($r->type === 'coach'
                ? 'bg-rose-50 text-rose-700 dark:bg-rose-900/40 dark:text-rose-200'
                : 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-200');
          $img = $r->image ?: 'data:image/svg+xml;utf8,' . rawurlencode('<svg xmlns="http://www.w3.org/2000/svg" width="64" height="40"><rect width="100%" height="100%" fill="#e5e7eb"/><text x="50%" y="50%" dy="4" fill="#6b7280" font-size="10" text-anchor="middle">No image</text></svg>');
        @endphp
        <li class="flex items-center gap-3 rounded-xl border border-gray-100 bg-white/70 p-2 shadow-sm ring-1 ring-black/5
                   dark:border-gray-800 dark:bg-gray-900/60 dark:ring-white/10">
          <img src="{{ $img }}" class="h-10 w-16 rounded-lg object-cover ring-1 ring-black/5 dark:ring-white/10" alt="">
          <div class="min-w-0 flex-1">
            <div class="truncate text-sm font-medium">{{ $r->title }}</div>
            <div class="mt-0.5 flex flex-wrap items-center gap-2 text-xs text-gray-600 dark:text-gray-300">
              <span class="truncate">by <span class="font-medium">{{ $r->buyer_name }}</span></span>
              <span class="opacity-50">•</span>
              <span class="opacity-75">{{ $r->created_at->diffForHumans() }}</span>
            </div>
          </div>
          <div class="flex shrink-0 flex-col items-end">
            <span class="rounded-full px-2 py-0.5 text-[10px] font-semibold {{ $badge }}">
              {{ strtoupper($r->type) }}
            </span>
            <span class="mt-1 text-sm font-semibold">
              @php
                // helper inline per format money senza helper globale
                $amount = number_format(($r->amount ?? 0)/100, 2, '.', '');
              @endphp
              {{ $r->currency }} {{ $amount }}
            </span>
          </div>
        </li>
      @endforeach
    </ul>
  @endif
</div>