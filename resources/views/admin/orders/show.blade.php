<x-admin-layout title="Order #{{ $order->id }}">
  <div class="mb-4 flex items-center justify-between">
    <h2 class="text-lg font-semibold">Order #{{ $order->id }}</h2>
    <a href="{{ route('admin.orders.index') }}" class="text-sm underline">Back to orders</a>
  </div>

  <div class="grid gap-6 md:grid-cols-3">
    {{-- LEFT: details --}}
    <div class="space-y-6 md:col-span-2">
      {{-- Summary --}}
      <div class="rounded-xl border bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <h3 class="mb-3 font-semibold">Summary</h3>

        @php
          $statusBadge = match($order->status){
            'paid'    => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-200',
            'pending' => 'bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-200',
            'canceled'=> 'bg-rose-50 text-rose-700 dark:bg-rose-900/30 dark:text-rose-200',
            default   => 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300',
          };
        @endphp

        <dl class="grid grid-cols-2 gap-x-6 gap-y-3 text-sm">
          <dt class="text-gray-500 dark:text-gray-400">User</dt>
          <dd class="text-gray-900 dark:text-gray-100">
            {{ optional($order->user)->name ?? '—' }}
            <span class="text-xs text-gray-500 dark:text-gray-400">({{ optional($order->user)->email ?? '—' }})</span>
          </dd>

          <dt class="text-gray-500 dark:text-gray-400">Total</dt>
          <dd class="font-medium text-gray-900 dark:text-gray-100">
            {{ number_format($order->amount_cents/100, 2, ',', '.') }} {{ $order->currency }}
          </dd>

          <dt class="text-gray-500 dark:text-gray-400">Provider</dt>
          <dd class="uppercase text-gray-900 dark:text-gray-100">{{ $order->provider ?? '—' }}</dd>

          <dt class="text-gray-500 dark:text-gray-400">Status</dt>
          <dd>
            <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $statusBadge }}">
              {{ ucfirst($order->status) }}
            </span>
          </dd>

          <dt class="text-gray-500 dark:text-gray-400">Created</dt>
          <dd class="text-gray-900 dark:text-gray-100">{{ $order->created_at?->format('d/m/Y H:i') }}</dd>

          <dt class="text-gray-500 dark:text-gray-400">Updated</dt>
          <dd class="text-gray-900 dark:text-gray-100">{{ $order->updated_at?->format('d/m/Y H:i') }}</dd>
        </dl>
      </div>

      {{-- Cart / Meta --}}
      <div class="rounded-xl border bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <h3 class="mb-3 font-semibold">Cart / Meta</h3>
        @php
          $meta     = $order->meta ?? [];
          $cart     = $meta['cart'] ?? [];
          $customer = $meta['customer'] ?? [];
        @endphp

        <h4 class="mb-2 text-sm font-semibold text-gray-600 dark:text-gray-300">Products</h4>
        <ul class="space-y-3">
          @forelse($cart as $it)
            <li class="flex items-center justify-between text-sm">
              <div class="flex items-center gap-3">
                @if(!empty($it['image']))
                  <img src="{{ $it['image'] }}" class="h-10 w-10 rounded object-cover" alt="">
                @else
                  <div class="h-10 w-10 rounded bg-gray-200 dark:bg-gray-700"></div>
                @endif
                <div>
                  <div class="font-medium text-gray-900 dark:text-gray-100">
                    {{ $it['name'] ?? ($it['type'] ?? 'Item') }}
                  </div>
                  @if(!empty($it['meta']['duration']))
                    <div class="text-xs text-gray-500 dark:text-gray-400">
                      Duration: {{ $it['meta']['duration'] }}
                    </div>
                  @endif
                </div>
              </div>
              <div class="font-medium text-gray-900 dark:text-gray-100">
                {{ number_format((($it['unit_amount_cents'] ?? 0)*($it['qty'] ?? 1))/100, 2, ',', '.') }}
                {{ $it['currency'] ?? $order->currency }}
              </div>
            </li>
          @empty
            <li class="text-sm text-gray-500 dark:text-gray-400">No products in meta.</li>
          @endforelse
        </ul>

        <h4 class="mt-4 mb-2 text-sm font-semibold text-gray-600 dark:text-gray-300">Customer</h4>
        @if($customer)
          <div class="text-sm text-gray-900 dark:text-gray-100">
            <div><span class="text-gray-500 dark:text-gray-400">Name:</span> {{ $customer['full_name'] ?? '—' }}</div>
            <div><span class="text-gray-500 dark:text-gray-400">Email:</span> {{ $customer['email'] ?? '—' }}</div>
          </div>
        @else
          <div class="text-sm text-gray-500 dark:text-gray-400">No data.</div>
        @endif
      </div>
    </div>

    {{-- RIGHT: actions --}}
    <aside class="space-y-6">
      <div class="rounded-xl border bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <h3 class="mb-3 font-semibold">Actions</h3>

        @if($order->status !== 'paid')
          <form method="POST" action="{{ route('admin.orders.markPaid',$order) }}">
            @csrf
            <button class="w-full rounded-xl bg-emerald-600 px-3 py-2 text-white shadow
                           hover:opacity-90">Mark as paid</button>
          </form>
        @endif

        @if($order->status !== 'canceled')
          <form class="mt-2" method="POST" action="{{ route('admin.orders.markCanceled',$order) }}">
            @csrf
            <button class="w-full rounded-xl bg-rose-600 px-3 py-2 text-white shadow
                           hover:opacity-90">Mark as cancelled</button>
          </form>
        @endif
      </div>

      {{-- Provider raw payload (opzionale) --}}
      {{-- @if($order->provider_response)
        <div class="rounded-xl border bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
          <h3 class="mb-3 font-semibold">Provider response</h3>
          <pre class="max-h-80 overflow-auto text-xs leading-relaxed">
{{ is_array($order->provider_response) ? json_encode($order->provider_response, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES) : $order->provider_response }}
          </pre>
        </div>
      @endif --}}
    </aside>
  </div>
</x-admin-layout>