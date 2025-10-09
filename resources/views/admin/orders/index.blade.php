<x-admin-layout title="Orders">
  {{-- Intro "semplice" (puoi toglierla se non ti serve) --}}
  <div class="mb-6 rounded border border-gray-200 bg-gray-50 p-4 text-sm text-gray-600
              dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200">
    Track and manage all your orders. Click an order to view details, mark it as paid or cancel it.
  </div>

  <div class="mb-4 flex items-center justify-between">
    <h2 class="text-lg font-semibold">Orders</h2>
    {{-- spazio per future filtri/azioni --}}
  </div>

  <div class="overflow-hidden rounded-xl border bg-white shadow-sm
              dark:border-gray-800 dark:bg-gray-900">
    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
      <thead class="bg-gray-50 text-left text-xs font-semibold uppercase text-gray-500
                     dark:bg-gray-950 dark:text-gray-300">
        <tr>
          <th class="px-4 py-3">#</th>
          <th class="px-4 py-3">Customer</th>
          <th class="px-4 py-3">Total</th>
          <th class="px-4 py-3">Status</th>
          <th class="px-4 py-3">Created</th>
          <th class="px-4 py-3 text-right">Actions</th>
        </tr>
      </thead>

      <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
        @foreach($orders as $o)
          @php
            $badge = match($o->status){
              'paid'    => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-200',
              'pending' => 'bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-200',
              'canceled'=> 'bg-rose-50 text-rose-700 dark:bg-rose-900/30 dark:text-rose-200',
              default   => 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300',
            };
          @endphp
          <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/60">
            <td class="px-4 py-3 font-medium text-gray-900 dark:text-gray-100">#{{ $o->id }}</td>

            <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">
              {{ data_get($o->meta, 'customer.full_name', '—') }}
              @if($mail = data_get($o->meta,'customer.email'))
                <div class="text-xs text-gray-500 dark:text-gray-400">{{ $mail }}</div>
              @endif
            </td>

            <td class="px-4 py-3 font-medium text-gray-900 dark:text-gray-100">
              {{ number_format($o->amount_cents/100, 2, ',', '.') }} {{ $o->currency }}
            </td>

            <td class="px-4 py-3">
              <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $badge }}">
                {{ ucfirst($o->status) }}
              </span>
            </td>

            <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">
              {{ $o->created_at->format('d/m/Y H:i') }}
            </td>

            <td class="px-4 py-3 text-right">
              <div class="inline-flex items-center gap-2">
                <a href="{{ route('admin.orders.show',$o) }}"
                   class="inline-flex items-center rounded-full border px-3 py-1.5 text-xs font-semibold
                          hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800">
                  View
                </a>

                <form action="{{ route('admin.orders.destroy',$o) }}" method="POST"
                      onsubmit="return confirm('Sei sicuro di voler eliminare questo ordine?');" class="inline">
                  @csrf @method('DELETE')
                  <button type="submit"
                          class="inline-flex items-center rounded-full border px-3 py-1.5 text-xs font-semibold text-rose-600
                                 hover:bg-rose-50 dark:border-gray-700 dark:text-rose-300 dark:hover:bg-rose-900/20">
                    Delete
                  </button>
                </form>
              </div>
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>

  <div class="mt-4">
    {{ $orders->links() }}
  </div>
</x-admin-layout>