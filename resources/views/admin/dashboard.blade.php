{{-- resources/views/admin/dashboard.blade.php --}}
<x-admin-layout title="Dashboard - Admin">
  {{-- =========================
       HERO HEADER / SUMMARY
     ========================= --}}
  <section class="mb-6 rounded-3xl border border-gray-200 bg-white/70 p-5 shadow-sm ring-1 ring-black/5 backdrop-blur md:p-6 dark:border-gray-800 dark:bg-gray-900/60">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
      <div class="flex items-center gap-3">
        <div class="grid h-12 w-12 place-items-center rounded-2xl text-white shadow"
             style="background: linear-gradient(135deg, color-mix(in oklab, var(--accent) 85%, #000 0%) 0%, color-mix(in oklab, var(--accent) 55%, #000 0%) 100%);">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M5 17h14v2H5zM4 7l4 4 4-6 4 6 4-4v8H4z"/></svg>
        </div>
        <div>
          <h1 class="font-orbitron text-xl leading-tight text-gray-900 md:text-2xl dark:text-gray-50">Welcome back</h1>
          <p class="text-sm text-gray-600 dark:text-gray-300">Quick look at your store performance</p>
        </div>
      </div>

      <div class="flex gap-2">
        <a href="{{ route('admin.orders.index') }}"
           class="rounded-xl border px-3 py-2 text-sm font-medium hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800">
          View Orders
        </a>
        <a href="{{ route('admin.packs.create') }}"
           class="rounded-xl bg-[color:var(--accent)] px-3 py-2 text-sm font-medium text-white hover:opacity-90">
          New Pack
        </a>
      </div>
    </div>
  </section>

  {{-- =========================
       KPI STRIP (animated)
     ========================= --}}
  @php
    $revenue7d      = round($revenue7d ?? 0);
    $orders7d       = (int)($orders7d ?? 0);
    $customers      = (int)($customers ?? 0);
    $aov7d          = round($aov7d ?? 0);
    $rev7dDelta     = (float)($rev7dDelta ?? 0);
    $ord7dDelta     = (float)($ord7dDelta ?? 0);
    $customersDelta = (float)($customersDelta ?? 0);
    $aov7dDelta     = (float)($aov7dDelta ?? 0);

    $kpis = [
      ['label'=>'Revenue (7d)', 'value'=>$revenue7d, 'suffix'=>'€', 'delta'=>$rev7dDelta, 'icon'=>'cash'],
      ['label'=>'Orders (7d)',  'value'=>$orders7d,  'suffix'=>'',  'delta'=>$ord7dDelta, 'icon'=>'bag'],
      ['label'=>'Customers',    'value'=>$customers, 'suffix'=>'',  'delta'=>$customersDelta, 'icon'=>'users'],
      ['label'=>'AOV (7d)',     'value'=>$aov7d,    'suffix'=>'€', 'delta'=>$aov7dDelta, 'icon'=>'ticket'],
    ];
  @endphp

  <section class="mb-6 grid grid-cols-2 gap-3 md:grid-cols-4">
    @foreach($kpis as $k)
      @php $pos = ($k['delta'] ?? 0) >= 0; @endphp
      <div
        x-data="{n:0}"
        x-init="let t={{ (int)$k['value'] }}; let step=Math.ceil(t/18)||1; let i=setInterval(()=>{ n+=step; if(n>=t){n=t;clearInterval(i)} },30)"
        class="relative overflow-hidden rounded-2xl border border-gray-200 bg-white p-4 shadow-sm ring-1 ring-black/5 dark:border-gray-800 dark:bg-gray-900">
        <div class="flex items-center justify-between">
          <div class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ $k['label'] }}</div>
          <div class="opacity-60">
            @switch($k['icon'])
              @case('cash')   <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M3 6h18v12H3zM7 9h10v6H7z"/></svg>@break
              @case('bag')    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M6 7h12l-1 12H7L6 7zm4-2a2 2 0 114 0v2h-4V5z"/></svg>@break
              @case('users')  <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M16 11a4 4 0 10-8 0 4 4 0 008 0zM4 20a8 8 0 1116 0H4z"/></svg>@break
              @default        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="12" r="9"/></svg>
            @endswitch
          </div>
        </div>
        <div class="mt-2 flex items-end gap-1">
          <div class="text-2xl font-bold tracking-tight text-gray-900 dark:text-gray-50">
            <span x-text="n.toLocaleString('it-IT')"></span>{{ $k['suffix'] }}
          </div>
          <span class="mb-0.5 rounded-full px-2 py-0.5 text-[11px] font-semibold {{ $pos ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-200' : 'bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-200' }}">
            {{ $pos ? '▲' : '▼' }} {{ number_format(abs($k['delta'] ?? 0), 1) }}%
          </span>
        </div>
      </div>
    @endforeach
  </section>

  {{-- =========================
       TRENDS + HEALTH
     ========================= --}}
  @php
    $ordersPerDay = $ordersPerDay ?? [3,6,4,9,7,10,5];
    $max = max($ordersPerDay) ?: 1;
    $pts = collect($ordersPerDay)->map(fn($v,$i)=> ($i*100/(max(1,count($ordersPerDay)-1))).','. (100 - ($v/$max*100)) )->implode(' ');
    $orders7dPeakDay = $orders7dPeakDay ?? '—';
    $ordersPending = (int)($ordersPending ?? 0);
    $refunds30d    = (int)($refunds30d ?? 0);
    $newCustomers7d= (int)($newCustomers7d ?? 0);
  @endphp

  <section class="mb-8 grid grid-cols-1 gap-6 lg:grid-cols-3">
    {{-- Orders trend --}}
    <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm ring-1 ring-black/5 dark:border-gray-800 dark:bg-gray-900 lg:col-span-2">
      <div class="mb-3 flex items-center justify-between gap-3">
        <div>
          <h3 class="font-semibold text-gray-900 dark:text-gray-50">Orders — last 7 days</h3>
          <p class="text-xs text-gray-500 dark:text-gray-400">Peak day: {{ $orders7dPeakDay }}</p>
        </div>
        <a href="{{ route('admin.orders.index') }}" class="rounded-lg border px-2.5 py-1 text-xs hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800">Open</a>
      </div>

      <div class="relative h-36 w-full overflow-hidden rounded-xl border border-gray-100 bg-gray-50 dark:border-gray-800 dark:bg-gray-900/60">
        <svg viewBox="0 0 100 100" preserveAspectRatio="none" class="h-full w-full">
          <polyline points="{{ $pts }}" fill="none" stroke="currentColor" stroke-width="2" class="text-[color:var(--accent)] opacity-90"/>
        </svg>
        <div class="pointer-events-none absolute inset-0 bg-gradient-to-t from-white/70 to-transparent dark:from-gray-900/60"></div>
      </div>

      <div class="mt-3 flex flex-wrap items-center gap-2 text-xs">
        <span class="rounded-full bg-gray-100 px-2 py-0.5 text-gray-700 dark:bg-gray-800 dark:text-gray-200">Total: <strong>{{ array_sum($ordersPerDay) }}</strong></span>
        <span class="rounded-full bg-gray-100 px-2 py-0.5 text-gray-700 dark:bg-gray-800 dark:text-gray-200">Avg/day: <strong>{{ number_format(array_sum($ordersPerDay)/max(1,count($ordersPerDay)),1) }}</strong></span>
        <span class="rounded-full bg-gray-100 px-2 py-0.5 text-gray-700 dark:bg-gray-800 dark:text-gray-200">Max: <strong>{{ $max }}</strong></span>
      </div>
    </div>

    {{-- Health / quick facts --}}
    <div class="grid grid-cols-1 gap-4">
      <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm ring-1 ring-black/5 dark:border-gray-800 dark:bg-gray-900">
        <div class="mb-2 flex items-center justify-between">
          <h4 class="font-semibold">Pending / Processing</h4>
          <span class="rounded-full bg-amber-100 px-2 py-0.5 text-[11px] font-semibold text-amber-800 dark:bg-amber-900/40 dark:text-amber-200">{{ $ordersPending }}</span>
        </div>
        <p class="text-sm text-gray-600 dark:text-gray-300">Orders awaiting fulfillment.</p>
      </div>

      <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm ring-1 ring-black/5 dark:border-gray-800 dark:bg-gray-900">
        <div class="mb-2 flex items-center justify-between">
          <h4 class="font-semibold">Refunds (30d)</h4>
          <span class="rounded-full bg-rose-100 px-2 py-0.5 text-[11px] font-semibold text-rose-800 dark:bg-rose-900/40 dark:text-rose-200">{{ $refunds30d }}</span>
        </div>
        <p class="text-sm text-gray-600 dark:text-gray-300">Chargebacks or refunded orders.</p>
      </div>

      <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm ring-1 ring-black/5 dark:border-gray-800 dark:bg-gray-900">
        <div class="mb-2 flex items-center justify-between">
          <h4 class="font-semibold">New customers (7d)</h4>
          <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-[11px] font-semibold text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-200">{{ $newCustomers7d }}</span>
        </div>
        <p class="text-sm text-gray-600 dark:text-gray-300">First-time buyers this week.</p>
      </div>
    </div>
  </section>

  {{-- =========================
       CONTENT MIX — DONUT
     ========================= --}}
  @php
    // Fallback content mix (puoi sostituire con i tuoi conteggi reali)
    $packsCount     = (int)($packsCount     ?? 0);
    $servicesCount  = (int)($servicesCount  ?? 0);
    $buildersCount  = (int)($buildersCount  ?? 0);
    $heroesCount    = (int)($heroesCount    ?? 0);
    $slidesCount    = (int)($slidesCount    ?? 0);

    $contentMix = $contentMix ?? [
      ['label'=>'Packs',    'value'=>$packsCount,    'color'=>'#7c3aed'],
      ['label'=>'Services', 'value'=>$servicesCount, 'color'=>'#06b6d4'],
      ['label'=>'Builders', 'value'=>$buildersCount, 'color'=>'#10b981'],
      ['label'=>'Heroes',   'value'=>$heroesCount,   'color'=>'#f59e0b'],
      ['label'=>'Slides',   'value'=>$slidesCount,   'color'=>'#ef4444'],
    ];
    $contentTotal = collect($contentMix)->sum('value') ?: 1;

    // calcolo gradient del donut
    $acc=0; $stops=[];
    foreach ($contentMix as $row) {
      $pct = round(($row['value'] / $contentTotal) * 100);
      $from = $acc;
      $to   = min(100, $acc + $pct);
      $stops[] = "{$row['color']} {$from}% {$to}%";
      $acc = $to;
    }
    $donutGradient = 'conic-gradient('.implode(', ', $stops).')';
  @endphp

  <section class="grid grid-cols-1 gap-6 lg:grid-cols-3">
    <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm ring-1 ring-black/5 dark:border-gray-800 dark:bg-gray-900 lg:col-span-1">
      <div class="flex items-center justify-between">
        <h3 class="font-semibold">Content mix</h3>
        <span class="text-xs text-gray-500 dark:text-gray-400">Share by type</span>
      </div>

      <div class="mt-4 grid grid-cols-1 items-center gap-4 sm:grid-cols-2">
        {{-- Donut --}}
        <div class="mx-auto grid place-items-center">
          <div class="relative h-36 w-36">
            <div class="absolute inset-0 rounded-full" style="background: {{ $donutGradient }}"></div>
            <div class="absolute inset-3 rounded-full bg-white shadow-inner dark:bg-gray-900/70"></div>
            <div class="absolute inset-0 grid place-items-center">
              <div class="text-center">
                <div class="text-xs text-gray-500 dark:text-gray-400">Total</div>
                <div class="text-lg font-semibold">{{ $contentTotal }}</div>
              </div>
            </div>
          </div>
        </div>

        {{-- Legend --}}
        <div class="space-y-2">
          @foreach($contentMix as $row)
            @php $pct = round(($row['value'] / $contentTotal) * 100); @endphp
            <div class="flex items-center justify-between gap-3">
              <div class="flex items-center gap-2">
                <span class="h-3.5 w-3.5 rounded-full" style="background: {{ $row['color'] }}"></span>
                <span class="text-sm text-gray-700 dark:text-gray-200">{{ $row['label'] }}</span>
              </div>
              <span class="text-xs text-gray-500 dark:text-gray-400">{{ $row['value'] }} ({{ $pct }}%)</span>
            </div>
          @endforeach
        </div>
      </div>
    </div>

    {{-- SLOT libero per prossime metriche o tabella ultimi ordini --}}
    {{-- <div class="lg:col-span-2 grid grid-cols-1 gap-6">
      <div class="rounded-2xl border border-dashed border-gray-300 p-8 text-center text-sm text-gray-500 dark:border-gray-700 dark:text-gray-400">
        Add your next module here (e.g. latest orders table, top products, conversion funnel…)
      </div>
    </div>
  </section>
</x-admin-layout> --}}
