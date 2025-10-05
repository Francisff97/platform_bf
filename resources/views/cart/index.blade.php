<x-app-layout title="Cart">
  <x-slot name="header"><h1 class="text-2xl font-bold">Carrello</h1></x-slot>

  @if(empty($items))
    <p class="text-gray-500">Il carrello è vuoto.</p>
  @else
    <div class="overflow-hidden rounded-xl border bg-white shadow-sm dark:bg-gray-900 dark:border-gray-800">
      <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
        <thead class="text-left text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">
        <tr>
          <th class="px-4 py-3">Articolo</th>
          <th class="px-4 py-3">Prezzo</th>
          <th class="px-4 py-3">Qtà</th>
          <th class="px-4 py-3 text-right">Totale</th>
          <th class="px-4 py-3"></th>
        </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
        @foreach($items as $i => $it)
          <tr>
            <td class="px-4 py-3">
              <div class="flex items-center gap-3">
                @if($it['image']) <img src="{{ $it['image'] }}" class="h-12 w-12 rounded object-cover"> @endif
                <div class="font-medium">{{ $it['name'] }}</div>
              </div>
              @if(!empty($it['meta']['duration'])) <div class="text-xs text-gray-500">Durata: {{ $it['meta']['duration'] }}</div> @endif
            </td>
            <td class="px-4 py-3">{{ number_format($it['unit_amount_cents']/100,2,',','.') }} {{ $it['currency'] }}</td>
            <td class="px-4 py-3">{{ $it['qty'] }}</td>
            <td class="px-4 py-3 text-right font-semibold">
              {{ number_format(($it['unit_amount_cents']*$it['qty'])/100,2,',','.') }} {{ $it['currency'] }}
            </td>
            <td class="px-4 py-3 text-right">
              <form method="POST" action="{{ route('cart.remove',$i) }}">@csrf
                <button class="text-red-600 hover:underline">Rimuovi</button>
              </form>
            </td>
          </tr>
        @endforeach
        </tbody>
      </table>
    </div>

    <div class="mt-4 flex items-center justify-between">
      <form method="POST" action="{{ route('cart.clear') }}">@csrf
        <button class="text-sm underline">Svuota carrello</button>
      </form>
      <div class="text-xl font-bold">
        Totale: {{ number_format($totalCents/100,2,',','.') }} {{ $currency }}
      </div>
    </div>

    <div class="mt-6 text-right">
      <a href="{{ route('checkout.index') }}" class="rounded bg-[var(--accent)] px-5 py-2 text-white hover:opacity-90">Procedi al checkout</a>
    </div>
  @endif
</x-app-layout>