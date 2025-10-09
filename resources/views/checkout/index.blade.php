{{-- resources/views/checkout/index.blade.php --}}
<x-app-layout title="Checkout">
  @php
    if (!function_exists('format_money')) {
      function format_money($cents, $currency = 'EUR') {
        $amount = max(0, (int)$cents) / 100;
        return number_format($amount, 2, '.', ',') . ' ' . $currency;
      }
    }
  @endphp

  <x-slot name="header">
    <h1 class="text-2xl font-bold">Checkout</h1>
  </x-slot>

  @guest
    <div class="mb-4 rounded-xl border bg-white/70 p-3 text-center text-sm text-gray-700 shadow-sm
                dark:border-gray-800 dark:bg-gray-900/70 dark:text-gray-200">
      Don’t have an account?
      <a href="{{ route('register') }}" class="text-[var(--accent)] underline-offset-2 hover:underline">Sign up now</a>
    </div>
  @endguest

  <div class="grid gap-6 md:grid-cols-3">
    {{-- Left: customer + payment --}}
    <div class="md:col-span-2 space-y-6">
      {{-- Customer --}}
      <div class="rounded-2xl border bg-white/70 p-4 shadow-sm backdrop-blur
                  dark:border-gray-800 dark:bg-gray-900/70">
        <h2 class="mb-3 text-lg font-semibold">Customer details</h2>

        <form id="checkoutForm" class="grid gap-4 sm:grid-cols-2">
          @csrf
          <div>
            <label class="block text-sm text-gray-600 dark:text-gray-300">Full name</label>
            <input name="full_name"
                   class="mt-1 w-full rounded-xl border px-3 py-2 ring-1 ring-black/10
                          dark:bg-gray-900 dark:text-white dark:ring-white/10" required>
          </div>
          <div>
            <label class="block text-sm text-gray-600 dark:text-gray-300">Email</label>
            <input type="email" name="email"
                   class="mt-1 w-full rounded-xl border px-3 py-2 ring-1 ring-black/10
                          dark:bg-gray-900 dark:text-white dark:ring-white/10" required>
          </div>
        </form>
      </div>

      {{-- Payment --}}
      <div class="rounded-2xl border bg-white/70 p-4 shadow-sm backdrop-blur
                  dark:border-gray-800 dark:bg-gray-900/70">
        <h2 class="mb-3 text-lg font-semibold">Payment</h2>

        <div class="rounded-xl border border-dashed p-4 text-sm text-gray-600
                    dark:border-gray-800 dark:text-gray-300">
          <div id="paypal-buttons"></div>
        </div>

        {{-- PayPal SDK --}}
        <script src="https://www.paypal.com/sdk/js?client-id={{ urlencode(config('services.paypal.client_id')) }}&currency={{ $currency ?? 'EUR' }}&intent=capture"></script>

        <script>
          document.addEventListener('DOMContentLoaded', () => {
            const form = document.getElementById('checkoutForm');
            const fullNameInput = form.querySelector('input[name="full_name"]');
            const emailInput    = form.querySelector('input[name="email"]');

            function validForm() {
              const name  = (fullNameInput.value || '').trim();
              const email = (emailInput.value || '').trim();
              return name.length > 1 && /\S+@\S+\.\S+/.test(email);
            }

            if (!window.paypal) {
              console.error('PayPal SDK not loaded');
              alert('PayPal unavailable right now.');
              return;
            }

            paypal.Buttons({
              style: { layout: 'vertical', shape: 'rect', color: 'gold', label: 'paypal' },

              createOrder: async () => {
                if (!validForm()) {
                  alert('Please enter a valid full name and email.');
                  throw new Error('Invalid form');
                }
                const payload = {
                  full_name: fullNameInput.value.trim(),
                  email: emailInput.value.trim(),
                };

                const res = await fetch("{{ route('checkout.paypal') }}", {
                  method: "POST",
                  credentials: "include",
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
                  alert(`Order creation error (${res.status}).`);
                  throw new Error(`createOrder ${res.status}`);
                }

                const data = await res.json().catch(() => ({}));
                if (!data.id) {
                  console.error('createOrder: missing id in response', data);
                  alert('Order error: missing PayPal id.');
                  throw new Error('Missing id');
                }
                return data.id;
              },

              onApprove: async (data) => {
                try {
                  const url = "{{ route('checkout.capture') }}" + "?token=" + encodeURIComponent(data.orderID);
                  const res = await fetch(url, { credentials: "include" });
                  if (!res.ok) {
                    window.location.href = "{{ route('checkout.cancel') }}";
                    return;
                  }
                  window.location.href = "{{ route('checkout.success') }}";
                } catch (e) {
                  console.error('onApprove exception', e);
                  window.location.href = "{{ route('checkout.cancel') }}";
                }
              },

              onCancel: () => window.location.href = "{{ route('checkout.cancel') }}",
              onError:  (err) => { console.error('PayPal onError:', err); alert('PayPal error.'); }
            }).render('#paypal-buttons');
          });
        </script>
      </div>
    </div>

    {{-- Right: summary --}}
    <aside class="h-max rounded-2xl border bg-white/70 p-4 shadow-sm backdrop-blur
                  dark:border-gray-800 dark:bg-gray-900/70">
      <h2 class="mb-3 text-lg font-semibold">Order summary</h2>

      {{-- Mobile cards for items --}}
      <div class="grid gap-3 sm:hidden">
        @foreach($items as $it)
          <div class="rounded-xl bg-white/60 p-3 ring-1 ring-black/5 dark:bg-white/5 dark:ring-white/10">
            <div class="flex items-center justify-between text-sm">
              <div class="min-w-0">
                <div class="truncate font-medium">{{ $it['name'] }}</div>
                @if(!empty($it['meta']['duration']))
                  <div class="truncate text-xs opacity-70">Duration: {{ $it['meta']['duration'] }}</div>
                @endif
              </div>
              <div class="shrink-0 font-medium">
                {{ format_money($it['unit_amount_cents']*$it['qty'], $it['currency']) }}
              </div>
            </div>
          </div>
        @endforeach
      </div>

      {{-- Desktop simple list --}}
      <ul class="hidden space-y-3 sm:block">
        @foreach($items as $it)
          <li class="flex items-center justify-between text-sm">
            <div class="min-w-0">
              <div class="truncate font-medium">{{ $it['name'] }}</div>
              @if(!empty($it['meta']['duration']))
                <div class="truncate text-xs text-gray-500 dark:text-gray-400">
                  Duration: {{ $it['meta']['duration'] }}
                </div>
              @endif
            </div>
            <div class="shrink-0 font-medium">
              {{ format_money($it['unit_amount_cents']*$it['qty'], $it['currency']) }}
            </div>
          </li>
        @endforeach
      </ul>

      <div class="mt-4 border-t pt-3 text-right text-lg font-bold">
        Total: {{ format_money($totalCents, $currency) }}
      </div>

      <p class="mt-3 text-xs opacity-70">
        By completing the purchase you agree to the Terms and Privacy Policy.
      </p>
    </aside>
  </div>
</x-app-layout>