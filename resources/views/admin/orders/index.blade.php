<x-admin-layout title="Orders">
  <div class="mb-4 flex items-center justify-between">
    <h2 class="text-lg font-semibold">Orders</h2>
    {{-- (niente CTA qui, è solo listing) --}}
  </div>

  {{-- ===== MOBILE: CARD LIST ===== --}}
  <div class="grid grid-cols-1 gap-4 md:hidden">
    @forelse($orders as $o)
      <div class="overflow-hidden rounded-xl border bg-white shadow-sm ring-1 ring-black/5 dark:border-gray-800 dark:bg-gray-900 dark:ring-white/10">
        <div class="flex items-start justify-between gap-3 p-3">
          <div class="min-w-0">
            <div class="font-medium text-gray-900 dark:text-gray-100">
              #{{ $o->id }} • {{ $o->meta['customer']['full_name'] ?? '—' }}
            </div>
            <div class="text-xs text-gray-500 dark:text-gray-400">
              {{ $o->created_at->format('d/m/Y H:i') }}
            </div>
            <div class="mt-1 text-sm">
              <span class="font-semibold">
                {{ number_format($o->amount_cents/100,2,',','.') }} {{ $o->currency }}
              </span>
            </div>
          </div>

          <div class="shrink-0">
            <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold
              {{ $o->status==='paid' ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-200' :
                 ($o->status==='pending' ? 'bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-200' :
                 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300') }}">
              {{ ucfirst($o->status) }}
            </span>
          </div>
        </div>

        <div class="flex items-center justify-end gap-3 border-t p-3 text-sm dark:border-gray-800">
          <a href="{{ route('admin.orders.show',$o) }}" class="text-indigo-600 hover:underline">Have a look</a>
          <form action="{{ route('admin.orders.destroy',$o) }}" method="POST"
                onsubmit="return confirm('Sei sicuro di voler eliminare questo ordine?');">
            @csrf @method('DELETE')
            <button type="submit" class="text-rose-600 hover:underline">Delete</button>
          </form>
        </div>
      </div>
    @empty
      <div class="rounded-xl border bg-white p-4 text-center text-gray-500 shadow-sm dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300">
        No orders yet.
      </div>
    @endforelse
  </div>

  {{-- ===== DESKTOP: TABLE ===== --}}
  <div class="hidden overflow-hidden rounded-xl border bg-white shadow-sm dark:bg-gray-900 dark:border-gray-800 md:block">
    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
      <thead class="bg-gray-100 dark:bg-gray-900 dark:text-white">
        <tr>
          <th class="px-3 py-2 text-left">#</th>
          <th class="px-3 py-2 text-left">Customer</th>
          <th class="px-3 py-2 text-left">Total</th>
          <th class="px-3 py-2 text-left">Status</th>
          <th class="px-3 py-2 text-left">Created</th>
          <th class="px-3 py-2 text-right">Actions</th>
        </tr>
      </thead>
      <tbody>
        @foreach($orders as $o)
          <tr class="border-t hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-900/60">
            <td class="px-3 py-2 align-middle">#{{ $o->id }}</td>
            <td class="px-3 py-2 align-middle">{{ $o->meta['customer']['full_name'] ?? '—' }}</td>
            <td class="px-3 py-2 align-middle">
              {{ number_format($o->amount_cents/100,2,',','.') }} {{ $o->currency }}
            </td>
            <td class="px-3 py-2 align-middle">
              <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold
                {{ $o->status==='paid' ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-200' :
                   ($o->status==='pending' ? 'bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-200' :
                   'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300') }}">
                {{ ucfirst($o->status) }}
              </span>
            </td>
            <td class="px-3 py-2 align-middle">{{ $o->created_at->format('d/m/Y H:i') }}</td>
            <td class="px-3 py-2 align-middle text-right">
              <a href="{{ route('admin.orders.show',$o) }}" class="text-indigo-600 hover:underline mr-3">Have a look</a>
              <form action="{{ route('admin.orders.destroy',$o) }}" method="POST" class="inline"
                    onsubmit="return confirm('Sei sicuro di voler eliminare questo ordine?');">
                @csrf @method('DELETE')
                <button type="submit" class="text-rose-600 hover:underline">Delete</button>
              </form>
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>

  @if(method_exists($orders,'links'))
    <div class="mt-4">{{ $orders->links() }}</div>
  @endif
</x-admin-layout>