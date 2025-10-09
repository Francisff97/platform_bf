{{-- resources/views/cart/index.blade.php --}}
<x-app-layout title="Cart">
@php
  if (!function_exists('format_money')) {
    function format_money($cents, $currency='EUR') {
      $amount = max(0,(int)$cents)/100; return number_format($amount,2,'.',',').' '.$currency;
    }
  }
  if (!function_exists('is_coach')) {
    function is_coach($it){
      return (($it['type']??null)==='coach') || (($it['meta']['type']??null)==='coach') || !empty($it['meta']['is_coach']);
    }
  }
@endphp

<x-slot name="header"><h1 class="text-2xl font-bold">Cart</h1></x-slot>

@if(empty($items))
  <div class="rounded-xl border bg-white/70 p-6 text-center text-gray-600 shadow-sm
              dark:border-gray-800 dark:bg-gray-900/70 dark:text-gray-200">
    Your cart is empty.
  </div>
@else

  {{-- Mobile cards --}}
  <div class="grid gap-4 sm:hidden">
    @foreach($items as $i => $it)
      <div class="rounded-2xl border bg-white/70 p-3 shadow-sm backdrop-blur
                  dark:border-gray-800 dark:bg-gray-900/70">
        <div class="flex items-start gap-3">
          @if($it['image'])
            <img src="{{ $it['image'] }}" class="h-16 w-16 rounded-xl object-cover ring-1 ring-black/5 dark:ring-white/10" alt="">
          @endif
          <div class="min-w-0 flex-1">
            <div class="truncate text-sm font-semibold">{{ $it['name'] }}</div>
            @if(!empty($it['meta']['duration']))
              <div class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">Duration: {{ $it['meta']['duration'] }}</div>
            @endif

            <div class="mt-2 grid grid-cols-2 items-center text-sm">
              <div>
                <div class="opacity-70">Unit</div>
                <div class="font-medium">{{ format_money($it['unit_amount_cents'],$it['currency']) }}</div>
              </div>
              <div class="text-right">
                <div class="opacity-70">Qty</div>

                @if(is_coach($it))
                  <form method="POST" action="{{ route('cart.updateQty',$i) }}" class="inline-flex items-center gap-1">
                    @csrf
                    <button name="action" value="dec" class="h-7 w-7 rounded-lg ring-1 ring-black/10 hover:bg-black/5 dark:ring-white/10 dark:hover:bg-white/10">−</button>
                    <input type="number" name="qty" value="{{ $it['qty'] }}" min="1" max="99"
                           class="h-7 w-12 rounded-lg border px-2 text-center dark:bg-gray-900 dark:border-gray-800">
                    <button name="action" value="inc" class="h-7 w-7 rounded-lg ring-1 ring-black/10 hover:bg-black/5 dark:ring-white/10 dark:hover:bg-white/10">+</button>
                  </form>
                @else
                  <div class="font-medium">{{ $it['qty'] }}</div>
                @endif
              </div>
            </div>

            <div class="mt-2 text-right text-base font-semibold">
              {{ format_money($it['unit_amount_cents']*$it['qty'],$it['currency']) }}
            </div>
          </div>
        </div>

        <div class="mt-3 flex items-center justify-end gap-2">
          <form method="POST" action="{{ route('cart.remove',$i) }}">@csrf
            <button class="rounded-lg px-3 py-1.5 text-sm text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20">Remove</button>
          </form>
        </div>
      </div>
    @endforeach
  </div>

  {{-- Desktop table --}}
  <div class="hidden overflow-hidden rounded-2xl border bg-white/70 shadow-sm backdrop-blur
              dark:border-gray-800 dark:bg-gray-900/70 sm:block">
    <table class="min-w-full text-sm">
      <thead class="bg-gray-50/70 text-left text-xs font-semibold uppercase tracking-wide text-gray-600
                    dark:bg-gray-900/60 dark:text-gray-400">
        <tr>
          <th class="px-4 py-3">Item</th>
          <th class="px-4 py-3">Unit price</th>
          <th class="px-4 py-3">Qty</th>
          <th class="px-4 py-3 text-right">Total</th>
          <th class="px-4 py-3"></th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-100/70 dark:divide-gray-800">
        @foreach($items as $i => $it)
          <tr class="hover:bg-black/5 dark:hover:bg-white/5">
            <td class="px-4 py-3">
              <div class="flex items-center gap-3">
                @if($it['image'])
                  <img src="{{ $it['image'] }}" class="h-12 w-12 rounded-lg object-cover ring-1 ring-black/5 dark:ring-white/10" alt="">
                @endif
                <div>
                  <div class="font-medium">{{ $it['name'] }}</div>
                  @if(!empty($it['meta']['duration']))
                    <div class="text-xs text-gray-500 dark:text-gray-400">Duration: {{ $it['meta']['duration'] }}</div>
                  @endif
                </div>
              </div>
            </td>
            <td class="px-4 py-3">{{ format_money($it['unit_amount_cents'],$it['currency']) }}</td>
            <td class="px-4 py-3">
              @if(is_coach($it))
                <form method="POST" action="{{ route('cart.updateQty',$i) }}" class="inline-flex items-center gap-1">
                  @csrf
                  <button name="action" value="dec" class="h-7 w-7 rounded-lg ring-1 ring-black/10 hover:bg-black/5 dark:ring-white/10 dark:hover:bg-white/10">−</button>
                  <input type="number" name="qty" value="{{ $it['qty'] }}" min="1" max="99"
                         class="h-7 w-14 rounded-lg border px-2 text-center dark:bg-gray-900 dark:border-gray-800">
                  <button name="action" value="inc" class="h-7 w-7 rounded-lg ring-1 ring-black/10 hover:bg-black/5 dark:ring-white/10 dark:hover:bg-white/10">+</button>
                </form>
              @else
                {{ $it['qty'] }}
              @endif
            </td>
            <td class="px-4 py-3 text-right font-semibold">
              {{ format_money($it['unit_amount_cents']*$it['qty'],$it['currency']) }}
            </td>
            <td class="px-4 py-3 text-right">
              <form method="POST" action="{{ route('cart.remove',$i) }}">@csrf
                <button class="rounded-lg px-3 py-1.5 text-sm text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20">Remove</button>
              </form>
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>

  {{-- Footer --}}
  <div class="mt-4 flex flex-col-reverse items-stretch gap-3 sm:flex-row sm:items-center sm:justify-between">
    <form method="POST" action="{{ route('cart.clear') }}">@csrf
      <button class="rounded-lg px-3 py-2 text-sm opacity-80 ring-1 ring-black/10 hover:bg-black/5 dark:ring-white/10 dark:hover:bg-white/10">Empty cart</button>
    </form>
    <div class="flex items-center justify-between gap-3 sm:justify-end">
      <div class="text-xl font-bold">Total: {{ format_money($totalCents,$currency) }}</div>
      <a href="{{ route('checkout.index') }}"
         class="rounded-xl bg-[var(--accent)] px-5 py-2.5 text-white shadow-sm ring-1 ring-white/10 hover:opacity-95">Go to checkout</a>
    </div>
  </div>
@endif
</x-app-layout>