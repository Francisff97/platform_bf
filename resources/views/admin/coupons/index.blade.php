<x-admin-layout title="Coupons">
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

  <div class="mb-4 flex items-center justify-between">
    <div class="text-sm text-gray-600 dark:text-gray-300">
      Manage discount coupons (percentage or fixed amount).
    </div>
    <a href="{{ route('admin.coupons.create') }}"
       class="rounded bg-[var(--accent)] px-3 py-1.5 text-white hover:opacity-90">New coupon</a>
  </div>

  <div class="overflow-hidden rounded-2xl border bg-white/70 shadow-sm backdrop-blur dark:border-gray-800 dark:bg-gray-900/70">
    <table class="min-w-full text-sm">
      <thead class="bg-gray-50/70 text-left text-xs font-semibold uppercase tracking-wide text-gray-600 dark:bg-gray-900/60 dark:text-gray-400">
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
                {{ number_format($c->value_cents/100,2,'.',',') }} {{ optional(\App\Models\SiteSetting::first())->currency ?? 'EUR' }}
              @endif
            </td>
            <td class="px-3 py-2">
              <span class="rounded-full px-2 py-0.5 text-xs {{ $c->is_active ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-100' : 'bg-gray-200 text-gray-700 dark:bg-gray-800 dark:text-gray-300' }}">
                {{ $c->is_active ? 'Active' : 'Disabled' }}
              </span>
            </td>
            <td class="px-3 py-2">{{ $c->usage_count }} @if($c->max_uses)/ {{ $c->max_uses }} @endif</td>
            <td class="px-3 py-2">
              @if($c->starts_at) {{ $c->starts_at->format('Y-m-d') }} @endif – @if($c->ends_at) {{ $c->ends_at->format('Y-m-d') }} @endif
            </td>
            <td class="px-3 py-2 text-right">
              <div class="inline-flex items-center gap-2">
                <a href="{{ route('admin.coupons.edit',$c) }}" class="text-indigo-600 hover:underline">Edit</a>
                <form method="POST" action="{{ route('admin.coupons.toggle',$c) }}">@csrf
                  <button class="text-sm underline">{{ $c->is_active ? 'Disable' : 'Activate' }}</button>
                </form>
                <form method="POST" action="{{ route('admin.coupons.destroy',$c) }}"
                      onsubmit="return confirm('Delete coupon?')">@csrf @method('DELETE')
                  <button class="text-red-600 hover:underline">Delete</button>
                </form>
              </div>
            </td>
          </tr>
        @empty
          <tr><td colspan="7" class="px-3 py-6 text-center text-gray-500 dark:text-gray-300">No coupons yet.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div class="mt-3">{{ $coupons->links() }}</div>
</x-admin-layout>