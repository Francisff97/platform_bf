<x-app-layout title="Checkout">
  <x-slot name="header"><h1 class="text-2xl font-bold">Checkout</h1></x-slot>
  @guest
  <div class="mt-6 text-center">
    <p class="text-sm text-gray-600 dark:text-gray-300">
      Non hai un account?
      <a href="{{ route('register') }}" class="text-[var(--accent)] hover:underline">Registrati ora</a>
    </p>
  </div>
@endguest
  <div class="grid gap-6 md:grid-cols-3">
    <div class="md:col-span-2">
      <div class="rounded-xl border bg-white p-4 shadow-sm dark:bg-gray-900 dark:border-gray-800">
        <h2 class="mb-3 text-lg font-semibold">Dati cliente</h2>
        <form id="checkoutForm">
          @csrf
          <div class="grid gap-4 sm:grid-cols-2">
            <div>
              <label class="block text-sm text-gray-600 dark:text-white">Nome e Cognome</label>
              <input name="full_name" class="mt-1 w-full rounded border p-2 dark:text-gray-800" required>
            </div>
            <div>
              <label class="block text-sm text-gray-600 dark:text-white">Email</label>
              <input type="email" name="email" class="mt-1 w-full rounded border p-2 dark:text-gray-800" required>
            </div>
          </div>
        </form>
      </div>

      <div class="mt-6 rounded-xl border bg-white p-4 shadow-sm dark:bg-gray-900 dark:border-gray-800">
        <h2 class="mb-3 text-lg font-semibold">Pagamento</h2>
        <div id="paypal-buttons"></div>
        {{-- SDK PayPal --}}
<script src="https://www.paypal.com/sdk/js?client-id={{ urlencode(config('services.paypal.client_id')) }}&currency={{ $currency ?? 'EUR' }}&intent=capture"></script>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const fullNameInput = document.querySelector('input[name="full_name"]');
  const emailInput    = document.querySelector('input[name="email"]');

  if (!window.paypal) {
    console.error('PayPal SDK non caricato');
    alert('PayPal non disponibile');
    return;
  }

  function validForm() {
    const name  = (fullNameInput.value || '').trim();
    const email = (emailInput.value || '').trim();
    return name.length > 1 && /\S+@\S+\.\S+/.test(email);
  }

  paypal.Buttons({
    style: { layout: 'vertical', shape: 'rect', color: 'gold', label: 'paypal' },

    createOrder: async () => {
      if (!validForm()) {
        alert('Inserisci Nome e Email validi.');
        throw new Error('Invalid form');
      }

      const payload = {
        full_name: fullNameInput.value.trim(),
        email: emailInput.value.trim(),
      };

      const url = "{{ route('checkout.paypal') }}";
      console.log('createOrder → POST', url, payload);

      const res = await fetch(url, {
        method: "POST",
        credentials: "include", // <— IMPORTANTE per session/CSRF
        headers: {
          "Content-Type": "application/json",
          "X-CSRF-TOKEN": "{{ csrf_token() }}",
          "X-Requested-With": "XMLHttpRequest",
        },
        body: JSON.stringify(payload),
      });

      if (!res.ok) {
        const text = await res.text();
        console.error('createOrder FAIL', res.status, text);
        alert(`Errore creazione ordine (${res.status}). Guarda la console.`);
        throw new Error(`createOrder ${res.status}`);
      }

      const data = await res.json().catch(() => ({}));
      console.log('createOrder OK', data);
      if (!data.id) {
        console.error('createOrder: manca id nella risposta', data);
        alert('Errore: risposta PayPal senza id.');
        throw new Error('Missing id');
      }
      return data.id;
    },

    onApprove: async (data) => {
      try {
        console.log('onApprove', data);
        const url = "{{ route('checkout.capture') }}" + "?token=" + encodeURIComponent(data.orderID);
        console.log('capture → GET', url);

        const res = await fetch(url, { credentials: "include" });

        // Alcuni setup non seguono i redirect via fetch → forziamo la success qui
        if (!res.ok) {
          const text = await res.text();
          console.error('capture FAIL', res.status, text);
          window.location.href = "{{ route('checkout.cancel') }}";
          return;
        }

        console.log('capture OK, redirect a success');
        window.location.href = "{{ route('checkout.success') }}";
      } catch (e) {
        console.error('onApprove exception', e);
        window.location.href = "{{ route('checkout.cancel') }}";
      }
    },

    onCancel: () => {
      console.log('onCancel');
      window.location.href = "{{ route('checkout.cancel') }}";
    },

    onError: (err) => {
      console.error('PayPal onError:', err);
      alert('Errore PayPal (vedi console per dettagli).');
    }
  }).render('#paypal-buttons');
});
</script>
      </div>
    </div>

    <aside class="rounded-xl border bg-white p-4 shadow-sm dark:bg-gray-900 dark:border-gray-800">
      <h2 class="mb-3 text-lg font-semibold">Riepilogo</h2>
      <ul class="space-y-3">
        @foreach($items as $it)
          <li class="flex items-center justify-between">
            <div class="text-sm">
              <div class="font-medium">{{ $it['name'] }}</div>
              @if(!empty($it['meta']['duration']))<div class="text-xs text-gray-500">Durata: {{ $it['meta']['duration'] }}</div>@endif
            </div>
            <div class="text-sm font-medium">
              {{ number_format(($it['unit_amount_cents']*$it['qty'])/100,2,',','.') }} {{ $it['currency'] }}
            </div>
          </li>
        @endforeach
      </ul>
      <div class="mt-4 border-t pt-3 text-right text-lg font-bold">
        Totale: {{ number_format($totalCents/100,2,',','.') }} {{ $currency }}
      </div>
    </aside>
  </div>
</x-app-layout>