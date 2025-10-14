<x-admin-layout title="Dashboard - Admin">
  @php
    // fallback sicuri (nel caso il controller non passi i dati)
    $recentOrders = $recentOrders ?? collect();
    $topSelling   = $topSelling   ?? collect();
    $coupons      = $coupons      ?? collect();
  @endphp

  {{-- ===== HERO ===== --}}
  <section class="mb-6 rounded-3xl border border-gray-200 bg-white/70 p-5 shadow-sm ring-1 ring-black/5 backdrop-blur md:p-6 dark:border-gray-800 dark:bg-gray-900/60">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
      <div class="flex items-center gap-3">
        <div class="grid h-12 w-12 place-items-center rounded-2xl text-white shadow"
             style="background: linear-gradient(135deg, color-mix(in oklab, var(--accent) 85%, #000 0%) 0%, color-mix(in oklab, var(--accent) 55%, #000 0%) 100%);">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="currentColor"><path d="M5 17h14v2H5zM4 7l4 4 4-6 4 6 4-4v8H4z"/></svg>
        </div>
        <div>
          <h1 class="font-orbitron text-xl leading-tight text-gray-900 md:text-2xl dark:text-gray-50">Welcome back</h1>
          <p class="text-sm text-gray-600 dark:text-gray-300">Quick look at your store performance</p>
        </div>
      </div>
      <div class="flex gap-2">
        <a href="{{ route('admin.orders.index') }}" class="rounded-xl border px-3 py-2 text-sm font-medium hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800">View Orders</a>
        <a href="{{ route('admin.packs.create') }}" class="rounded-xl bg-[color:var(--accent)] px-3 py-2 text-sm font-medium text-white hover:opacity-90">New Pack</a>
      </div>
    </div>
  </section>

  {{-- ===== KPI STRIP ===== --}}
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
      <div x-data="{n:0}"
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

  {{-- ===== TRENDS + HEALTH ===== --}}
  @php
    $ordersPerDay    = $ordersPerDay ?? [3,6,4,9,7,10,5];
    $max             = max($ordersPerDay) ?: 1;
    $pts             = collect($ordersPerDay)->map(fn($v,$i)=> ($i*100/(max(1,count($ordersPerDay)-1))).','. (100 - ($v/$max*100)) )->implode(' ');
    $orders7dPeakDay = $orders7dPeakDay ?? '—';
    $ordersPending   = (int)($ordersPending ?? 0);
    $refunds30d      = (int)($refunds30d ?? 0);
    $newCustomers7d  = (int)($newCustomers7d ?? 0);
  @endphp

  <section class="mb-8 grid grid-cols-1 gap-6 lg:grid-cols-3">
    {{-- trend --}}
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

    {{-- health --}}
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

  {{-- ===== CONTENT MIX (donut) ===== --}}
  @php
    $packsCount=$packsCount??0;$servicesCount=$servicesCount??0;$buildersCount=$buildersCount??0;$heroesCount=$heroesCount??0;$slidesCount=$slidesCount??0;
    $contentMix = $contentMix ?? [
      ['label'=>'Packs',    'value'=>$packsCount,    'color'=>'#7c3aed'],
      ['label'=>'Services', 'value'=>$servicesCount, 'color'=>'#06b6d4'],
      ['label'=>'Builders', 'value'=>$buildersCount, 'color'=>'#10b981'],
      ['label'=>'Heroes',   'value'=>$heroesCount,   'color'=>'#f59e0b'],
      ['label'=>'Slides',   'value'=>$slidesCount,   'color'=>'#ef4444'],
    ];
    $contentTotal = collect($contentMix)->sum('value') ?: 1;
    $acc=0; $stops=[];
    foreach ($contentMix as $row) {
      $pct = round(($row['value'] / $contentTotal) * 100);
      $from = $acc; $to = min(100, $acc + $pct);
      $stops[] = "{$row['color']} {$from}% {$to}%";
      $acc = $to;
    }
    $donutGradient = 'conic-gradient('.implode(', ', $stops).')';
  @endphp

  <section class="grid grid-cols-1 gap-6 lg:grid-cols-3">
    <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm ring-1 ring-black/5 dark:border-gray-800 dark:bg-gray-900">
      <div class="flex items-center justify-between">
        <h3 class="font-semibold">Content mix</h3>
        <span class="text-xs text-gray-500 dark:text-gray-400">Share by type</span>
      </div>

      <div class="mt-4 grid grid-cols-1 items-center gap-4 sm:grid-cols-2">
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
  </section>

  {{-- ===== RECENT PURCHASES (full width slider) ===== --}}
  <section class="mb-8 w-full">
    <div class="rounded-2xl border border-gray-100 bg-white/70 shadow-sm ring-1 ring-black/5 dark:border-gray-800 dark:bg-gray-900/60 dark:ring-white/10">
      <div class="flex items-center justify-between px-4 py-3">
        <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-50">Recent purchases</h3>
        <a href="{{ route('admin.orders.index') }}" class="text-xs opacity-70 hover:opacity-100">View all</a>
      </div>

      <style>
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
        .edge-fade {
          -webkit-mask-image: linear-gradient(to right, transparent 0, black 32px, black calc(100% - 32px), transparent 100%);
          mask-image: linear-gradient(to right, transparent 0, black 32px, black calc(100% - 32px), transparent 100%);
        }
      </style>

      {{-- Desktop slider --}}
      <div class="relative hidden sm:block">
        <div x-data="rp()" x-init="init()" class="group relative">
          <div id="rp-track" class="edge-fade no-scrollbar overflow-x-auto whitespace-nowrap scroll-smooth px-4 pb-4" style="-webkit-overflow-scrolling:touch;">
            @forelse($recentOrders as $o)
              @php
                $cart  = $o->meta['cart'] ?? [];
                $line  = is_array($cart) ? ($cart[0] ?? null) : null;
                $img   = $line['image'] ?? null;
                $name  = $line['name']  ?? ('Order #'.$o->id);
                $type  = strtoupper($line['type'] ?? 'ITEM');
                $amt   = (int)($line['unit_amount_cents'] ?? $o->amount_cents);
                $cur   = strtoupper($line['currency'] ?? $o->currency ?? 'EUR');
                $extra = max(0, count($cart) - 1);
              @endphp

              <a href="{{ route('admin.orders.show', $o->id) }}"
                 class="inline-flex h-24 w-[420px] shrink-0 items-center gap-4 rounded-xl border border-gray-200 bg-white/85 p-4 shadow-sm ring-1 ring-black/5 transition hover:ring-black/10 dark:border-gray-800 dark:bg-gray-900/70 dark:ring-white/10 mr-3 align-top">
                <div class="h-16 w-16 overflow-hidden rounded-xl ring-1 ring-black/5 dark:ring-white/10">
                  @if($img)
                    <img src="{{ $img }}" alt="" class="h-full w-full object-cover">
                  @else
                    <div class="grid h-full w-full place-items-center bg-gray-200 text-xs text-gray-600 dark:bg-gray-800 dark:text-gray-300">IMG</div>
                  @endif
                </div>
                <div class="min-w-0 grow">
                  <div class="truncate text-sm font-semibold">{{ $name }}</div>
                  <div class="mt-0.5 flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                    <span>by {{ optional($o->user)->name ?? '—' }}</span><span aria-hidden="true">•</span>
                    <span>{{ $o->created_at->diffForHumans() }}</span>
                    @if($extra>0)
                      <span class="rounded-full bg-gray-100 px-1.5 py-0.5 text-[10px] font-semibold text-gray-700 dark:bg-gray-800 dark:text-gray-200">+{{ $extra }}</span>
                    @endif
                  </div>
                </div>
                <div class="text-right">
                  <span class="inline-flex items-center justify-center rounded-full bg-gray-100 px-2 py-0.5 text-[10px] font-semibold text-gray-700 dark:bg-gray-800 dark:text-gray-200">{{ $type }}</span>
                  <div class="mt-1 text-sm font-semibold">@money($amt, $cur)</div>
                </div>
              </a>
            @empty
              <div class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">No recent purchases.</div>
            @endforelse
          </div>

          <button type="button" @click="scroll(-1)"
                  class="absolute left-1 top-1/2 hidden -translate-y-1/2 rounded-full border bg-white/90 p-2 shadow-sm ring-1 ring-black/5 backdrop-blur hover:bg-white group-hover:block dark:border-gray-800 dark:bg-gray-900/80 dark:ring-white/10">‹</button>
          <button type="button" @click="scroll(1)"
                  class="absolute right-1 top-1/2 hidden -translate-y-1/2 rounded-full border bg-white/90 p-2 shadow-sm ring-1 ring-black/5 backdrop-blur hover:bg-white group-hover:block dark:border-gray-800 dark:bg-gray-900/80 dark:ring-white/10">›</button>
        </div>
      </div>

      {{-- Mobile list --}}
      <div class="sm:hidden divide-y dark:divide-gray-800">
        @foreach($recentOrders as $o)
          @php
            $cart = $o->meta['cart'] ?? [];
            $line = is_array($cart) ? ($cart[0] ?? null) : null;
            $img  = $line['image'] ?? null;
            $name = $line['name']  ?? ('Order #'.$o->id);
            $amt  = (int)($line['unit_amount_cents'] ?? $o->amount_cents);
            $cur  = strtoupper($line['currency'] ?? $o->currency ?? 'EUR');
          @endphp
          <a href="{{ route('admin.orders.show', $o->id) }}" class="flex items-center gap-3 px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-800">
            <div class="h-12 w-12 overflow-hidden rounded-lg ring-1 ring-black/5 dark:ring-white/10">
              @if($img)
                <img src="{{ $img }}" alt="" class="h-full w-full object-cover">
              @else
                <div class="grid h-full w-full place-items-center bg-gray-200 text-xs text-gray-600 dark:bg-gray-800 dark:text-gray-300">IMG</div>
              @endif
            </div>
            <div class="min-w-0 flex-1">
              <div class="truncate text-sm font-medium">{{ $name }}</div>
              <div class="text-xs text-gray-500 dark:text-gray-400">
                by {{ optional($o->user)->name ?? '—' }} • {{ $o->created_at->diffForHumans() }}
              </div>
            </div>
            <div class="text-right text-sm font-semibold">@money($amt, $cur)</div>
          </a>
        @endforeach
      </div>
    </div>
  </section>

  {{-- ===== TOP SELLING + COUPONS ===== --}}
  <section class="grid grid-cols-1 gap-6 lg:grid-cols-3">
    {{-- Top selling --}}
    <div class="lg:col-span-2 rounded-2xl border border-gray-200 bg-white p-5 shadow-sm ring-1 ring-black/5 dark:border-gray-800 dark:bg-gray-900">
      <div class="mb-3 flex items-center justify-between">
        <h3 class="font-semibold">Top selling (90d)</h3>
        <span class="text-xs text-gray-500 dark:text-gray-400">by revenue</span>
      </div>

      @if($topSelling->isEmpty())
        <div class="py-8 text-center text-sm text-gray-500 dark:text-gray-400">Not enough data yet.</div>
      @else
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-3">
          @foreach($topSelling as $row)
            <div class="flex items-center gap-3 rounded-xl border border-gray-200 bg-white/80 p-3 ring-1 ring-black/5 dark:border-gray-800 dark:bg-gray-900/70 dark:ring-white/10">
              <div class="h-12 w-12 overflow-hidden rounded-lg ring-1 ring-black/5 dark:ring-white/10">
                @if(!empty($row['image']))
                  <img src="{{ $row['image'] }}" class="h-full w-full object-cover" alt="">
                @else
                  <div class="grid h-full w-full place-items-center bg-gray-200 text-xs text-gray-600 dark:bg-gray-800 dark:text-gray-300">IMG</div>
                @endif
              </div>
              <div class="min-w-0 flex-1">
                <div class="truncate text-sm font-semibold">{{ $row['name'] }}</div>
                <div class="mt-0.5 flex items-center gap-2 text-[11px] text-gray-500 dark:text-gray-400">
                  <span class="rounded-full border px-1.5 py-0.5 dark:border-gray-700">{{ strtoupper($row['type']) }}</span>
                  <span>{{ $row['orders'] }} orders</span>
                  <span aria-hidden="true">•</span>
                  <span>{{ $row['qty'] }} items</span>
                </div>
              </div>
              <div class="text-right text-sm font-semibold">
                @money($row['revenue'], $row['currency'])
              </div>
            </div>
          @endforeach
        </div>
      @endif
    </div>

    {{-- Coupons --}}
    @php $siteCurrency = optional(\App\Models\SiteSetting::first())->currency ?? 'EUR'; @endphp
    <div class="rounded-2xl border border-gray-200 bg-white/70 p-5 shadow-sm ring-1 ring-black/5 dark:border-gray-800 dark:bg-gray-900/60">
      <div class="mb-3 flex items-center justify-between">
        <h3 class="font-semibold text-gray-900 dark:text-gray-50">Coupons</h3>
        @if(Route::has('admin.coupons.index'))
          <a href="{{ route('admin.coupons.index') }}" class="text-xs opacity-70 hover:opacity-100">Manage</a>
        @endif
      </div>

      <div class="overflow-x-auto rounded-xl border border-gray-100 dark:border-gray-800">
        <table class="min-w-full text-sm">
          <thead class="bg-gray-50 text-xs uppercase text-gray-500 dark:bg-gray-900 dark:text-gray-400">
            <tr>
              <th class="px-3 py-2 text-left">Code</th>
              <th class="px-3 py-2 text-left">Type</th>
              <th class="px-3 py-2 text-left">Value</th>
              <th class="px-3 py-2 text-left">Usage</th>
              <th class="px-3 py-2 text-left">Validity</th>
              <th class="px-3 py-2 text-left">Status</th>
              <th class="px-3 py-2"></th>
            </tr>
          </thead>
          <tbody class="divide-y dark:divide-gray-800">
            @forelse($coupons as $c)
              @php
                $valueLabel = $c->type === 'percent'
                  ? (int)($c->value ?? 0).'%'
                  : \App\Support\Money::formatCents((int)($c->value_cents ?? 0), $siteCurrency);

                $status = $c->is_active ? 'Active' : 'Disabled';
                if ($c->is_active && $c->starts_at && $c->starts_at->isFuture()) $status = 'Scheduled';
                if ($c->ends_at && $c->ends_at->isPast()) $status = 'Expired';

                $badgeCls = [
                  'Active'    => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-200',
                  'Scheduled' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-200',
                  'Expired'   => 'bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-200',
                  'Disabled'  => 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-200',
                ][$status];

                $used  = (int)($c->usage_count ?? 0);
                $limit = $c->max_uses;
                $pct   = $limit ? min(100, round($used * 100 / max(1,$limit))) : null;
              @endphp

              <tr class="hover:bg-gray-50/60 dark:hover:bg-gray-800/40">
                <td class="px-3 py-2 font-mono text-[13px]">{{ $c->code }}</td>
                <td class="px-3 py-2">
                  <span class="rounded-full bg-gray-100 px-2 py-0.5 text-[10px] font-semibold text-gray-700 dark:bg-gray-800 dark:text-gray-200">
                    {{ strtoupper($c->type) }}
                  </span>
                </td>
                <td class="px-3 py-2 font-medium">{{ $valueLabel }}</td>
                <td class="px-3 py-2">
                  <div class="flex items-center gap-2">
                    <span>{{ $used }}</span>
                    @if(!is_null($limit))
                      <span class="opacity-60">/ {{ (int)$limit }}</span>
                      <div class="ml-2 h-1.5 w-24 overflow-hidden rounded-full bg-gray-200 dark:bg-gray-800">
                        <div class="h-full" style="width: {{ $pct }}%; background: color-mix(in oklab, var(--accent) 85%, #000 0%);"></div>
                      </div>
                    @endif
                  </div>
                </td>
                <td class="px-3 py-2">
                  {{ $c->starts_at ? $c->starts_at->toDateString() : '—' }}
                  <span class="opacity-50">→</span>
                  {{ $c->ends_at ? $c->ends_at->toDateString() : '—' }}
                </td>
                <td class="px-3 py-2">
                  <span class="rounded-full px-2 py-0.5 text-[10px] font-semibold {{ $badgeCls }}">{{ $status }}</span>
                </td>
                <td class="px-3 py-2 text-right">
                  @if(Route::has('admin.coupons.edit'))
                    <a href="{{ route('admin.coupons.edit', $c->id) }}"
                       class="rounded-lg border px-2 py-1 text-xs hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800">Edit</a>
                  @endif
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="7" class="px-3 py-6 text-center text-sm text-gray-500 dark:text-gray-400">No coupons found.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </section>

  {{-- ==== helpers Recent purchases ==== --}}
  <script>
    function rp(){
      return {
        step: 480, _el:null, _down:false, _sx:0, _sl:0,
        init(){
          this._el = document.getElementById('rp-track');
          const el=this._el;
          if(!el) return;
          el.addEventListener('pointerdown', e => { this._down=true; this._sx=e.pageX; this._sl=el.scrollLeft; el.setPointerCapture(e.pointerId); });
          el.addEventListener('pointermove', e => { if(!this._down) return; el.scrollLeft = this._sl - (e.pageX - this._sx); });
          ['pointerup','pointerleave','pointercancel'].forEach(t => el.addEventListener(t, ()=> this._down=false));
        },
        scroll(dir){ this._el?.scrollBy({ left: dir * this.step, behavior:'smooth' }); }
      }
    }
  </script>
</x-admin-layout>