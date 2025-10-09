<x-admin-layout title="Dashboard - Admin">
  {{-- Top note --}}
  <div class="mb-6 rounded-2xl border border-gray-200 bg-gradient-to-br from-gray-50 to-white p-5 text-sm text-gray-700 shadow-sm dark:border-gray-700 dark:from-gray-800 dark:to-gray-900 dark:text-gray-100">
    <div class="flex items-start gap-3">
      <svg class="mt-0.5 h-5 w-5 text-[color:var(--accent)]" viewBox="0 0 24 24" fill="none" stroke="currentColor">
        <path d="M12 9v4m0 4h.01M12 3l9 4v6c0 5-4 8-9 8s-9-3-9-8V7l9-4Z" stroke-width="1.5"/>
      </svg>
      <p>Welcome to your admin dashboard. Get a quick overview and jump into the sections you manage most.</p>
    </div>
  </div>

  {{-- KPI / Stats --}}
  <div class="grid grid-cols-1 gap-5 md:grid-cols-3">
    <x-stat label="Packs"    :value="$packsCount ?? 0" />
    <x-stat label="Services" :value="$servicesCount ?? 0" />
    <x-stat label="Builders" :value="$buildersCount ?? 0" />
    <x-stat label="Heroes"   :value="$heroesCount ?? 0" />
    <x-stat label="Slides"   :value="$slidesCount ?? 0" />
    <x-stat label="Users"    :value="$usersCount ?? 0" />
  </div>

  @php
    // --- Fallback / computed data ------------------------------------------
    $ordersPerDay = $ordersPerDay ?? [3,6,4,9,7,10,5];
    $ordersMax    = max($ordersPerDay) ?: 1;

    $contentMix = $contentMix ?? [
      ['label'=>'Packs',    'value'=>$packsCount   ?? 0, 'color'=>'#7c3aed'],
      ['label'=>'Services', 'value'=>$servicesCount?? 0, 'color'=>'#06b6d4'],
      ['label'=>'Builders', 'value'=>$buildersCount?? 0, 'color'=>'#10b981'],
      ['label'=>'Heroes',   'value'=>$heroesCount  ?? 0, 'color'=>'#f59e0b'],
      ['label'=>'Slides',   'value'=>$slidesCount  ?? 0, 'color'=>'#ef4444'],
    ];
    $contentTotal = collect($contentMix)->sum('value') ?: 1;

    // Donut gradient string
    $acc = 0;
    $stops = [];
    foreach ($contentMix as $row) {
      $pct = round(($row['value'] / $contentTotal) * 100);
      $from = $acc;
      $to   = min(100, $acc + $pct);
      $stops[] = "{$row['color']} {$from}% {$to}%";
      $acc = $to;
    }
    $donutGradient = 'conic-gradient('.implode(', ', $stops).')';
  @endphp

  {{-- Actions + charts --}}
  <div class="mt-8 grid grid-cols-1 gap-6 xl:grid-cols-3">

    {{-- Quick cards (glass + gradient border) --}}
    <x-card class="group relative overflow-hidden transition hover:-translate-y-0.5 hover:shadow-lg dark:bg-gray-900 dark:text-white">
      <div class="pointer-events-none absolute inset-0 rounded-2xl opacity-60 " style="background: radial-gradient(600px 200px at 100% -20%, color-mix(in oklab, var(--accent) 28%, transparent) 0%, transparent 60%);"></div>
      <div class="flex items-center justify-between">
        <h3 class="font-semibold">Manage Packs</h3>
        <a href="{{ route('admin.packs.create') }}" class="rounded-xl bg-[color:var(--accent)] px-3 py-1.5 text-white text-sm transition hover:opacity-90">New Pack</a>
      </div>
      <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">Create and publish packs. Mark items as “featured”.</p>
      <a class="mt-3 inline-flex items-center text-[color:var(--accent)] hover:underline" href="{{ route('admin.packs.index') }}">
        Go to Packs →
      </a>
    </x-card>

    <x-card class="transition hover:-translate-y-0.5 hover:shadow-lg">
      <div class="flex items-center justify-between">
        <h3 class="font-semibold">Manage Services</h3>
        <a href="{{ route('admin.services.create') }}" class="rounded-xl bg-[color:var(--accent)] px-3 py-1.5 text-white text-sm transition hover:opacity-90">New</a>
      </div>
      <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">Use the “order” field to control listing.</p>
      <a class="mt-3 inline-flex items-center text-[color:var(--accent)] hover:underline" href="{{ route('admin.services.index') }}">
        Go to Services →
      </a>
    </x-card>

    <x-card class="transition hover:-translate-y-0.5 hover:shadow-lg">
      <div class="flex items-center justify-between">
        <h3 class="font-semibold">Manage Builders</h3>
        <a href="{{ route('admin.builders.create') }}" class="rounded-xl bg-[color:var(--accent)] px-3 py-1.5 text-white text-sm transition hover:opacity-90">New</a>
      </div>
      <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">Add/edit builders and their skills.</p>
      <a class="mt-3 inline-flex items-center text-[color:var(--accent)] hover:underline" href="{{ route('admin.builders.index') }}">
        Go to Builders →
      </a>
    </x-card>

    <x-card class="transition hover:-translate-y-0.5 hover:shadow-lg">
      <div class="flex items-center justify-between">
        <h3 class="font-semibold">Manage Heroes</h3>
        <a href="{{ route('admin.heroes.create') }}" class="rounded-xl bg-[color:var(--accent)] px-3 py-1.5 text-white text-sm transition hover:opacity-90">New</a>
      </div>
      <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">Curate hero content and visuals.</p>
      <a class="mt-3 inline-flex items-center text-[color:var(--accent)] hover:underline" href="{{ route('admin.heroes.index') }}">
        Go to Heroes →
      </a>
    </x-card>

    <x-card class="transition hover:-translate-y-0.5 hover:shadow-lg">
      <div class="flex items-center justify-between">
        <h3 class="font-semibold">Manage Slides</h3>
        <a href="{{ route('admin.slides.create') }}" class="rounded-xl bg-[color:var(--accent)] px-3 py-1.5 text-white text-sm transition hover:opacity-90">New</a>
      </div>
      <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">Control homepage sliders.</p>
      <a class="mt-3 inline-flex items-center text-[color:var(--accent)] hover:underline" href="{{ route('admin.slides.index') }}">
        Go to Slides →
      </a>
    </x-card>

    {{-- Accent promo --}}
    <div class="rounded-2xl p-5 text-white shadow-sm lg:col-span-1" style="background: linear-gradient(135deg, color-mix(in oklab, var(--accent) 85%, #000 0%) 0%, color-mix(in oklab, var(--accent) 55%, #000 0%) 100%);">
      <div class="flex items-start justify-between gap-3">
        <div>
          <h3 class="font-semibold">About page sections</h3>
          <p class="mt-1 text-sm/6 opacity-90">Create and manage the sections for your “About us” page here.</p>
        </div>
        <svg class="h-6 w-6 opacity-90" viewBox="0 0 24 24" fill="none" stroke="currentColor">
          <path d="M12 6v6l4 2" stroke-width="1.8"/>
          <circle cx="12" cy="12" r="9" stroke-width="1.4"/>
        </svg>
      </div>
      <a href="{{ route('admin.about.index') }}" class="mt-3 inline-flex items-center rounded-xl bg-white/15 px-3 py-1.5 text-sm font-medium transition hover:bg-white/25">
        Go to About sections →
      </a>
    </div>

    {{-- Orders last 7 days (animated bars) --}}
    <x-card class="lg:col-span-2">
      <div class="flex items-center justify-between">
        <h3 class="font-semibold">Orders — last 7 days</h3>
        <span class="text-xs text-gray-500 dark:text-gray-400">Auto-scaled</span>
      </div>

      <div class="mt-4">
        <div class="h-32 w-full rounded-2xl border border-gray-100 bg-gray-50 p-3 dark:border-gray-800 dark:bg-gray-900/60">
          <div class="flex h-full items-end gap-3">
            @foreach($ordersPerDay as $v)
              @php
                $targetH = max(6, intval(($v / $ordersMax) * 100)); // %
              @endphp
              <div class="relative flex-1">
                {{-- Initial height small; if Alpine is present, we animate to target --}}
                <div
                  x-data="{ h: 8 }"
                  x-init="setTimeout(()=>{ h={{ $targetH }} }, 50)"
                  class="mx-auto w-7 rounded-t bg-[color:var(--accent)] shadow-sm transition-all duration-700 ease-out will-change-transform"
                  :style="`height:${h}%`"
                  style="height: 8%;">
                </div>
                <div class="absolute inset-x-0 -bottom-6 text-center text-[11px] text-gray-500 dark:text-gray-400">{{ $v }}</div>
              </div>
            @endforeach
          </div>
        </div>
      </div>
    </x-card>

    {{-- Content mix: donut + legend --}}
    <x-card class="lg:col-span-1">
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
    </x-card>
  </div>
</x-admin-layout>
