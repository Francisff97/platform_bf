{{-- resources/views/admin/coupons/index.blade.php --}}
<x-admin-layout title="Coupons">
  {{-- Flash messages --}}
  @if (session('success'))
    <div class="mb-4 rounded border border-green-300 bg-green-50 px-3 py-2 text-sm text-green-800">
      {{ session('success') }}
    </div>
  @endif
  @if (session('error'))
    <div class="mb-4 rounded border border-red-300 bg-red-50 px-3 py-2 text-sm text-red-700">
      {{ session('error') }}
    </div>
  @endif

  {{-- Intro --}}
  <div class="mb-4 rounded-xl border bg-white/70 p-4 text-sm text-gray-700 shadow-sm backdrop-blur
              dark:border-gray-800 dark:bg-gray-900/70 dark:text-gray-200">
    Create and manage discount coupons for your store. You can choose between a
    <span class="font-medium">percentage</span> discount (e.g. <code>10%</code>) or a
    <span class="font-medium">fixed amount</span> discount (e.g. <code>€30</code>).
    Schedule start/end dates and limit maximum uses.
  </div>

  {{-- Top actions --}}
  <div class="mb-4 flex items-center justify-between gap-2">
    <div class="text-xs sm:text-sm opacity-80">
      Total: <span class="font-medium">{{ number_format($coupons->total()) }}</span> coupon(s)
    </div>
    <a href="{{ route('admin.coupons.create') }}"
       class="rounded-xl bg-[var(--accent)] px-3 py-1.5 text-white shadow-sm ring-1 ring-white/10 hover:opacity-90">
      New coupon
    </a>
  </div>

  @php
    $currency = optional(\App\Models\SiteSetting::first())->currency ?? 'EUR';
    $fmt = function($cents) use ($currency) {
      $amount = number_format(max(0,(int)$cents)/100, 2, '.', ',');
      return $amount.' '.$currency;
    };
  @endphp

  {{-- Mobile: cards --}}
  <div class="grid gap-3 sm:hidden">
    @forelse($coupons as $c)
      <div class="rounded-2xl border bg-white/70 p-3 shadow-sm backdrop-blur
                  dark:border-gray-800 dark:bg-gray-900/70">
        <div class="flex items-start justify-between gap-3">
          <div class="min-w-0">
            <div class="flex items-center gap-2">
              <div class="truncate text-base font-semibold">{{ $c->code }}</div>
              <span class="shrink-0 rounded-full px-2 py-0.5 text-[11px] font-semibold
                {{ $c->is_active ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-200'
                                 : 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300' }}">
                {{ $c->is_active ? 'Active' : 'Disabled' }}
              </span>
            </div>

            <div class="mt-1 text-xs opacity-80">
              Type:
              <span class="font-medium">{{ ucfirst($c->type) }}</span>
              @if($c->type==='percent')
                — Value: <span class="font-medium">{{ $c->value }}%</span>
              @else
                — Value: <span class="font-medium">{{ $fmt($c->value_cents) }}</span>
              @endif
            </div>

            <div class="mt-1 text-xs opacity-80">
              Usage: <span class="font-medium">{{ $c->usage_count }}</span>
              @if($c->max_uses) / <span class="font-medium">{{ $c->max_uses }}</span> @endif
            </div>

            <div class="mt-1 text-xs opacity-80">
              Window:
              <span class="font-medium">
                {{ $c->starts_at?->format('Y-m-d') ?: '—' }}
                –
                {{ $c->ends_at?->format('Y-m-d') ?: '—' }}
              </span>
            </div>
          </div>

          {{-- Quick action: edit --}}
          <a href="{{ route('admin.coupons.edit',$c) }}"
             class="shrink-0 rounded-lg px-2.5 py-1.5 text-xs ring-1 ring-black/10 hover:bg-black/5
                    dark:ring-white/10 dark:hover:bg-white/10">
            Edit
          </a>
        </div>

        <div class="mt-3 grid grid-cols-2 gap-2">
          <form method="POST" action="{{ route('admin.coupons.toggle',$c) }}">@csrf
            <button class="w-full rounded-lg px-3 py-2 text-sm
                           ring-1 ring-black/10 hover:bg-black/5
                           dark:ring-white/10 dark:hover:bg-white/10">
              {{ $c->is_active ? 'Disable' : 'Activate' }}
            </button>
          </form>

          <form method="POST" action="{{ route('admin.coupons.destroy',$c) }}"
                onsubmit="return confirm('Delete coupon?')">
            @csrf @method('DELETE')
            <button class="w-full rounded-lg px-3 py-2 text-sm text-red-600
                           ring-1 ring-red-200 hover:bg-red-50
                           dark:ring-red-800 dark:hover:bg-red-900/20">
              Delete
            </button>
          </form>
        </div>
      </div>
    @empty
      <div class="rounded-xl border p-6 text-center text-sm text-gray-600
                  dark:border-gray-800 dark:text-gray-300">
        No coupons yet.
      </div>
    @endforelse
  </div>

  {{-- Desktop: table --}}
  <div class="hidden overflow-hidden rounded-2xl border bg-white/70 shadow-sm backdrop-blur
              dark:border-gray-800 dark:bg-gray-900/70 sm:block">
    <table class="min-w-full text-sm">
      <thead class="bg-gray-50/70 text-left text-xs font-semibold uppercase tracking-wide text-gray-600
                    dark:bg-gray-900/60 dark:text-gray-400">
        <tr>
          <th class="px-3 py-2">Code</th>
          <th class="px-3 py-2">Type</th>
          <th class="px-3 py-2">Value</th>
          <th class="px-3 py-2">Active</th>
          <th class="px-3 py-2">Usage</th>
          <th class="px-3 py-2">Window</th>
          <th class="px-3 py-2 text-right">Actions</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-100/70 dark:divide-gray-800">
        @forelse($coupons as $c)
          <tr class="hover:bg-black/5 dark:hover:bg-white/5">
            <td class="px-3 py-2 font-semibold">{{ $c->code }}</td>
            <td class="px-3 py-2">{{ ucfirst($c->type) }}</td>
            <td class="px-3 py-2">
              @if($c->type==='percent')
                {{ $c->value }}%
              @else
                {{ $fmt($c->value_cents) }}
              @endif
            </td>
            <td class="px-3 py-2">
              <span class="rounded-full px-2 py-0.5 text-xs
                {{ $c->is_active ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-100'
                                 : 'bg-gray-200 text-gray-700 dark:bg-gray-800 dark:text-gray-300' }}">
                {{ $c->is_active ? 'Active' : 'Disabled' }}
              </span>
            </td>
            <td class="px-3 py-2">
              {{ $c->usage_count }} @if($c->max_uses)/ {{ $c->max_uses }} @endif
            </td>
            <td class="px-3 py-2">
              {{ $c->starts_at?->format('Y-m-d') }} – {{ $c->ends_at?->format('Y-m-d') }}
            </td>
            <td class="px-3 py-2 text-right">
              <div class="inline-flex items-center gap-2">
                <a href="{{ route('admin.coupons.edit',$c) }}"
                   class="rounded-lg px-2.5 py-1.5 text-sm ring-1 ring-black/10 hover:bg-black/5
                          dark:ring-white/10 dark:hover:bg-white/10">
                  Edit
                </a>
                <form method="POST" action="{{ route('admin.coupons.toggle',$c) }}">@csrf
                  <button class="text-sm underline">{{ $c->is_active ? 'Disable' : 'Activate' }}</button>
                </form>
                <form method="POST" action="{{ route('admin.coupons.destroy',$c) }}"
                      onsubmit="return confirm('Delete coupon?')">
                  @csrf @method('DELETE')
                  <button class="text-red-600 hover:underline">Delete</button>
                </form>
              </div>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="7" class="px-3 py-6 text-center text-gray-500 dark:text-gray-300">
              No coupons yet.
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  {{-- Pagination --}}
  <div class="mt-3">{{ $coupons->links() }}</div>
</x-admin-layout>