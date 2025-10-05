<x-app-layout>
  <x-slot name="header"><h1 class="text-2xl font-bold">Payment cancelled</h1></x-slot>

  <div class="rounded-xl border bg-white p-4 shadow-sm dark:bg-gray-900 dark:border-gray-800">
    @if(!empty($order))
      <p class="text-gray-700 dark:text-gray-200">
        Order #{{ $order->id }} was cancelled. We’re sorry—if you’d like to continue shopping, you can return to your cart.
      </p>
    @else
      <p class="text-gray-700 dark:text-gray-200">
        Your PayPal payment was cancelled. We’re sorry—if you’d like to continue shopping, you can return to your cart.
      </p>
    @endif

    <div class="mt-4 flex flex-wrap gap-3">
      <a href="{{ route('cart.index') }}"
         class="inline-flex items-center rounded bg-[var(--accent)] px-4 py-2 text-white hover:opacity-90">
        Go to cart
      </a>
      <a href="{{ route('packs.public') }}"
         class="inline-flex items-center rounded border px-4 py-2 hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800">
        Continue shopping
      </a>
    </div>
  </div>
</x-app-layout>