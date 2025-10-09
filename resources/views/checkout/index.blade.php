{{-- resources/views/checkout/index.blade.php --}}
<x-app-layout title="Checkout">
@php
  if (!function_exists('format_money')) {
    function format_money($c,$cur='EUR'){ return number_format(max(0,(int)$c)/100,2,'.',',').' '.$cur; }
  }
  if (!function_exists('is_coach')) {
    function is_coach($it){
      return (($it['type']??null)==='coach') || (($it['meta']['type']??null)==='coach') || !empty($it['meta']['is_coach']);
    }
  }
  // opzionale: valori passati dal controller
  $coupon = $coupon ?? (session('coupon') ?: null);
  $discountCents = $discountCents ?? 0;
  $payableCents  = ($totalCents - $discountCents);
@endphp

<x-slot name="header"><h1 class="text-2xl font-bold">Checkout</h1></x-slot>

@guest
  <div class="mb-4 rounded-xl border bg-white/70 p-3 text-center text-sm text-gray-700 shadow-sm
              dark:border-gray-800 dark:bg-gray-900/70 dark:text-gray-200">
    Don’t have an account?
    <a href="{{ route('register') }}" class="text-[var(--accent)] underline-offset-2 hover:underline">Sign up now</a>
  </div>
@endguest

<div class="grid gap-6 md:grid-cols-3">
  <div class="md:col-span-2 space-y-6">
    {{-- Customer --}}
    <div class="rounded-2xl border bg-white/70 p-4 shadow-sm backdrop-blur dark:border-gray-800 dark:bg-gray-900/70">
      <h2 class="mb-3 text-lg font-semibold">Customer details</h2>
      <form id="checkoutForm" class="grid gap-4 sm:grid-cols-2">
        @csrf
        <div>
          <label class="block text-sm text-gray-600 dark:text-gray-300">Full name</label>
          <input name="full_name" class="mt-1 w-full rounded-xl border px-3 py-2 ring-1 ring-black/10 dark:bg-gray-900 dark:text-white dark:ring-white/10" required>
        </div>
        <div>
          <label class="block text-sm text-gray-600 dark:text-gray-300">Email</label>
          <input type="email" name="email" class="mt-1 w-full rounded-xl border px-3 py-2 ring-1 ring-black/10 dark:bg-gray-900 dark:text-white dark:ring-white/10" required>
        </div>
      </form>
    </div>

    {{-- Coupon --}}
    <div class="rounded-2xl border bg-white/70 p-4 shadow-sm backdrop-blur dark:border-gray-800 dark:bg-gray-900/70">
      <h2 class="mb-3 text-lg font-semibold">Coupon</h2>

      @if($coupon)
        <div class="flex items-center justify-between rounded-xl bg-emerald-50 px-3 py-2 text-sm text-emerald-800 ring-1 ring-emerald-200 dark:bg-emerald-900/30 dark:text-emerald-100 dark:ring-emerald-800">
          <div>Applied: <strong>{{ $coupon['code'] }}</strong> ({{ $coupon['type']==='percent' ? $coupon['value'].'%' : format_money($coupon['value']*100,$currency) }} off)</div>
          <form method="POST" action="{{ route('checkout.coupon.remove') }}">@csrf
            <button class="rounded px-2 py-1 text-xs ring-1 ring-emerald-300 hover:bg-emerald-100/50 dark:ring-emerald-700 dark:hover:bg-emerald-800/40">Remove</button>
          </form>
        </div>
      @else
        <form class="flex items-center gap-2" method="POST" action="{{ route('checkout.coupon.apply') }}">
          @csrf
          <input name="code" placeholder="Enter coupon (e.g. BASEFORGE)" class="h-10 flex-1 rounded-xl border px-3 ring-1 ring-black/10 dark:bg-gray-900 dark:text-white dark:ring-white/10">
          <button class="h-10 rounded-xl bg-[var(--accent)] px-4 text-white ring-1 ring-white/10 hover:opacity-95">Apply</button>
        </form>
      @endif
    </div>

    {{-- Payment --}}
    <div class="rounded-2xl border bg-white/70 p-4 shadow-sm backdrop-blur dark:border-gray-800 dark:bg-gray-900/70">
      <h2 class="mb-3 text-lg font-semibold">Payment</h2>
      <div id="paypal-buttons"></div>

      <script src="https://www.paypal.com/sdk/js?client-id={{ urlencode(config('services.paypal.client_id')) }}&currency={{ $currency ?? 'EUR' }}&intent=capture"></script>
      <script>
        document.addEventListener('DOMContentLoaded', () => {
          const fullNameInput = document.querySelector('input[name="full_name"]');
          const emailInput    = document.querySelector('input[name="email"]');
          function validForm(){const n=(fullNameInput.value||'').trim(),e=(emailInput.value||'').trim();return n.length>1&&/\S+@\S+\.\S+/.test(e);}

          if(!window.paypal){alert('PayPal unavailable right now.');return;}

          paypal.Buttons({
            style:{layout:'vertical',shape:'rect',color:'gold',label:'paypal'},
            createOrder: async ()=>{
              if(!validForm()){alert('Please enter a valid full name and email.');throw new Error('Invalid form');}
              const payload={full_name:fullNameInput.value.trim(),email:emailInput.value.trim()};
              const res=await fetch("{{ route('checkout.paypal') }}",{method:"POST",credentials:"include",headers:{"Content-Type":"application/json","X-CSRF-TOKEN":"{{ csrf_token() }}","X-Requested-With":"XMLHttpRequest"},body:JSON.stringify(payload)});
              if(!res.ok){alert('Order creation error.');throw new Error('createOrder');}
              const data=await res.json(); if(!data.id) throw new Error('Missing id'); return data.id;
            },
            onApprove: async (data)=>{const url="{{ route('checkout.capture') }}?token="+encodeURIComponent(data.orderID);const r=await fetch(url,{credentials:"include"});window.location.href=r.ok?"{{ route('checkout.success') }}":"{{ route('checkout.cancel') }}"},
            onCancel: ()=>window.location.href="{{ route('checkout.cancel') }}",
            onError:  ()=>alert('PayPal error.')
          }).render('#paypal-buttons');
        });
      </script>
    </div>
  </div>

  {{-- Summary --}}
  <aside class="h-max rounded-2xl border bg-white/70 p-4 shadow-sm backdrop-blur dark:border-gray-800 dark:bg-gray-900/70">
    <h2 class="mb-3 text-lg font-semibold">Order summary</h2>

    {{-- Items + qty (coach only) --}}
    <div class="space-y-3">
      @foreach($items as $idx => $it)
        <div class="flex items-center justify-between rounded-xl bg-white/60 p-3 ring-1 ring-black/5 dark:bg-white/5 dark:ring-white/10">
          <div class="min-w-0">
            <div class="truncate text-sm font-medium">{{ $it['name'] }}</div>
            @if(!empty($it['meta']['duration']))
              <div class="truncate text-xs opacity-70">Duration: {{ $it['meta']['duration'] }}</div>
            @endif
          </div>
          <div class="flex items-center gap-2">
            @if(is_coach($it))
              <form method="POST" action="{{ route('cart.updateQty',$idx) }}" class="hidden sm:inline-flex items-center gap-1">
                @csrf
                <button name="action" value="dec" class="h-6 w-6 rounded-md ring-1 ring-black/10 hover:bg-black/5 dark:ring-white/10 dark:hover:bg-white/10">−</button>
                <input type="number" name="qty" value="{{ $it['qty'] }}" min="1" max="99" class="h-7 w-12 rounded-md border px-2 text-center dark:bg-gray-900 dark:border-gray-800">
                <button name="action" value="inc" class="h-6 w-6 rounded-md ring-1 ring-black/10 hover:bg-black/5 dark:ring-white/10 dark:hover:bg-white/10">+</button>
              </form>
            @endif
            <div class="min-w-[90px] text-right text-sm font-medium">
              {{ format_money($it['unit_amount_cents']*$it['qty'],$it['currency']) }}
            </div>
          </div>
        </div>
      @endforeach
    </div>

    <div class="mt-4 space-y-1 text-sm">
      <div class="flex items-center justify-between opacity-80">
        <span>Subtotal</span>
        <span>{{ format_money($totalCents,$currency) }}</span>
      </div>

      @if($coupon && $discountCents>0)
        <div class="flex items-center justify-between text-emerald-600 dark:text-emerald-300">
          <span>Coupon ({{ $coupon['code'] }})</span>
          <span>− {{ format_money($discountCents,$currency) }}</span>
        </div>
      @endif

      <div class="border-t pt-2 text-right text-lg font-bold">
        Total: {{ format_money($payableCents,$currency) }}
      </div>
    </aside>
</div>
</x-app-layout>