<x-admin-layout title="Order #{{ $order->id }}">
  <div class="mb-4 flex items-center justify-between">
    <h2 class="text-lg font-semibold">Order #{{ $order->id }}</h2>
    <a href="{{ route('admin.orders.index') }}" class="text-sm underline">Back to orders</a>
  </div>

  <div class="grid gap-6 md:grid-cols-3">
    <div class="md:col-span-2 space-y-6">
      <div class="rounded-xl border bg-white p-4 shadow-sm dark:bg-gray-900 dark:border-gray-800">
        <h3 class="mb-3 font-semibold">Resume</h3>
        <dl class="grid grid-cols-2 gap-2 text-sm">
          <dt class="text-gray-500">Users</dt>
          <dd>{{ optional($order->user)->name ?? '—' }} ({{ optional($order->user)->email ?? '—' }})</dd>

          <dt class="text-gray-500">Total</dt>
          <dd>{{ number_format($order->amount_cents/100,2,',','.') }} {{ $order->currency }}</dd>

          <dt class="text-gray-500">Provider</dt>
          <dd>{{ strtoupper($order->provider ?? '—') }}</dd>

          <dt class="text-gray-500">Status</dt>
          <dd>
            <span class="rounded-full px-2.5 py-0.5 text-xs font-medium
              {{ $order->status==='paid' ? 'bg-emerald-50 text-emerald-700' :
                 ($order->status==='pending' ? 'bg-amber-50 text-amber-700' : 'bg-gray-100 text-gray-700') }}">
              {{ ucfirst($order->status) }}
            </span>
          </dd>

          <dt class="text-gray-500">Created</dt>
          <dd>{{ $order->created_at?->format('d/m/Y H:i') }}</dd>

          <dt class="text-gray-500">Updated</dt>
          <dd>{{ $order->updated_at?->format('d/m/Y H:i') }}</dd>
        </dl>
      </div>

      <div class="rounded-xl border bg-white p-4 shadow-sm dark:bg-gray-900 dark:border-gray-800">
        <h3 class="mb-3 font-semibold">Cart / Meta</h3>
        @php
          $meta = $order->meta ?? [];
          $cart = $meta['cart'] ?? [];
          $customer = $meta['customer'] ?? [];
        @endphp

        <h4 class="mb-2 text-sm font-semibold text-gray-600">Products</h4>
        <ul class="space-y-3">
          @forelse($cart as $it)
            <li class="flex items-center justify-between text-sm">
              <div class="flex items-center gap-3">
                @if(!empty($it['image']))
                  <img src="{{ $it['image'] }}" class="h-10 w-10 rounded object-cover">
                @endif
                <div>
                  <div class="font-medium">{{ $it['name'] ?? ($it['type'] ?? 'Item') }}</div>
                  @if(!empty($it['meta']['duration']))
                    <div class="text-xs text-gray-500">Duration: {{ $it['meta']['duration'] }}</div>
                  @endif
                </div>
              </div>
              <div class="text-sm font-medium">
                {{ number_format((($it['unit_amount_cents'] ?? 0)*($it['qty'] ?? 1))/100,2,',','.') }}
                {{ $it['currency'] ?? $order->currency }}
              </div>
            </li>
          @empty
            <li class="text-sm text-gray-500">No product into the meta.</li>
          @endforelse
        </ul>

        <h4 class="mt-4 mb-2 text-sm font-semibold text-gray-600">Customers</h4>
        @if($customer)
          <div class="text-sm">
            <div><span class="text-gray-500">Name:</span> {{ $customer['full_name'] ?? '—' }}</div>
            <div><span class="text-gray-500">Email:</span> {{ $customer['email'] ?? '—' }}</div>
          </div>
        @else
          <div class="text-sm text-gray-500">No datas.</div>
        @endif
      </div>
    </div>

    <aside class="space-y-6">
      <div class="rounded-xl border bg-white p-4 shadow-sm dark:bg-gray-900 dark:border-gray-800">
        <h3 class="mb-3 font-semibold">Azioni</h3>
        @if($order->status !== 'paid')
          <form method="POST" action="{{ route('admin.orders.markPaid',$order) }}">
            @csrf
            <button class="w-full rounded bg-emerald-600 px-3 py-2 text-white hover:opacity-90">Mark as paid</button>
          </form>
        @endif
        @if($order->status !== 'canceled')
          <form class="mt-2" method="POST" action="{{ route('admin.orders.markCanceled',$order) }}">
            @csrf
            <button class="w-full rounded bg-red-600 px-3 py-2 text-white hover:opacity-90">Mark as cancelled</button>
          </form>
        @endif
      </div>

      {{-- @if($order->provider_response)
        <div class="rounded-xl border bg-white p-4 shadow-sm dark:bg-gray-900 dark:border-gray-800">
          <h3 class="mb-3 font-semibold">Provider response</h3>
          <pre class="max-h-80 overflow-auto text-xs">{{ is_array($order->provider_response) ? json_encode($order->provider_response, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES) : $order->provider_response }}</pre>
        </div>
      @endif --}}
    </aside>
  </div>
</x-admin-layout>