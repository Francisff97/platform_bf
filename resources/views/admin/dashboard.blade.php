<x-admin-layout title="Dashboard">
  {{-- Top note --}}
  <div class="bg-gray-50 p-4 rounded border border-gray-200 dark:bg-gray-800 dark:border-gray-700 text-sm text-gray-600 dark:text-gray-100 mb-6">
    Welcome to your admin dashboard. Get a quick overview and jump into the sections you manage most.
  </div>

  {{-- KPI / Stats --}}
  <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
    <x-stat label="Packs"    :value="$packsCount ?? 0" />
    <x-stat label="Services" :value="$servicesCount ?? 0" />
    <x-stat label="Builders" :value="$buildersCount ?? 0" />
    <x-stat label="Heroes"   :value="$heroesCount ?? 0" />
    <x-stat label="Slides"   :value="$slidesCount ?? 0" />
    <x-stat label="Users"    :value="$usersCount ?? 0" />
  </div>

  {{-- Quick actions + small charts --}}
  @php
    // Fallback demo data if not provided by the controller
    $ordersPerDay = $ordersPerDay ?? [3,6,4,9,7,10,5];   // 7 bars
    $ordersMax    = max($ordersPerDay) ?: 1;

    $contentMix = $contentMix ?? [
      ['label'=>'Packs',    'value'=>$packsCount   ?? 0, 'color'=>'#7c3aed'],
      ['label'=>'Services', 'value'=>$servicesCount?? 0, 'color'=>'#06b6d4'],
      ['label'=>'Builders', 'value'=>$buildersCount?? 0, 'color'=>'#10b981'],
      ['label'=>'Heroes',   'value'=>$heroesCount  ?? 0, 'color'=>'#f59e0b'],
      ['label'=>'Slides',   'value'=>$slidesCount  ?? 0, 'color'=>'#ef4444'],
    ];
    $contentTotal = collect($contentMix)->sum('value') ?: 1;
  @endphp

  <div class="mt-8 grid grid-cols-1 gap-6 lg:grid-cols-3">
    {{-- Quick cards --}}
<x-card class="lg:col-span-1">
  <div class="flex items-center justify-between">
    <h3 class="font-semibold">Manage Packs</h3>
    <a href="{{ route('admin.packs.create') }}"
       class="rounded bg-[var(--accent)] px-3 py-1.5 text-white text-sm hover:opacity-90">
      New Pack
    </a>
  </div>
  <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">
    Create and publish packs. Mark items as “featured” to highlight them on the homepage.
  </p>
  <a class="mt-3 inline-flex items-center text-[var(--accent)] hover:underline"
     href="{{ route('admin.packs.index') }}">Go to Packs →</a>
</x-card>

<x-card class="lg:col-span-1">
  <div class="flex items-center justify-between">
    <h3 class="font-semibold">Manage Services</h3>
    <a href="{{ route('admin.services.create') }}"
       class="rounded bg-[var(--accent)] px-3 py-1.5 text-white text-sm hover:opacity-90">
      New
    </a>
  </div>
  <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">
    Use the “order” field to control the listing position.
  </p>
  <a class="mt-3 inline-flex items-center text-[var(--accent)] hover:underline"
     href="{{ route('admin.services.index') }}">Go to Services →</a>
</x-card>

<x-card class="lg:col-span-1">
  <div class="flex items-center justify-between">
    <h3 class="font-semibold">Manage Builders</h3>
    <a href="{{ route('admin.builders.create') }}"
       class="rounded bg-[var(--accent)] px-3 py-1.5 text-white text-sm hover:opacity-90">
      New
    </a>
  </div>
  <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">
    Add/edit builders and their skills.
  </p>
  <a class="mt-3 inline-flex items-center text-[var(--accent)] hover:underline"
     href="{{ route('admin.builders.index') }}">Go to Builders →</a>
</x-card>

<x-card class="lg:col-span-1">
  <div class="flex items-center justify-between">
    <h3 class="font-semibold">Manage Heroes</h3>
    <a href="{{ route('admin.heroes.create') }}"
       class="rounded bg-[var(--accent)] px-3 py-1.5 text-white text-sm hover:opacity-90">
      New
    </a>
  </div>
  <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">
    Curate hero content and visuals.
  </p>
  <a class="mt-3 inline-flex items-center text-[var(--accent)] hover:underline"
     href="{{ route('admin.heroes.index') }}">Go to Heroes →</a>
</x-card>

<x-card class="lg:col-span-1">
  <div class="flex items-center justify-between">
    <h3 class="font-semibold">Manage Slides</h3>
    <a href="{{ route('admin.slides.create') }}"
       class="rounded bg-[var(--accent)] px-3 py-1.5 text-white text-sm hover:opacity-90">
      New
    </a>
  </div>
  <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">
    Control homepage sliders with ordering and visibility.
  </p>
  <a class="mt-3 inline-flex items-center text-[var(--accent)] hover:underline"
     href="{{ route('admin.slides.index') }}">Go to Slides →</a>
</x-card>

{{-- NEW: About sections (accent background) --}}
<div class="rounded-xl p-4 lg:col-span-1"
     style="background: var(--accent); color: #fff;">
  <div class="flex items-start justify-between gap-3">
    <div>
      <h3 class="font-semibold">About page sections</h3>
      <p class="mt-1 text-sm opacity-90">
        Create and manage the sections for your “About us” page here.
      </p>
    </div>
    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 opacity-90" viewBox="0 0 24 24" fill="none" stroke="currentColor">
      <path d="M12 6v6l4 2" stroke-width="1.8"/>
      <circle cx="12" cy="12" r="9" stroke-width="1.4"/>
    </svg>
  </div>
  <a href="{{ route('admin.about.index') }}"
     class="mt-3 inline-flex items-center rounded bg-white/15 px-3 py-1.5 text-sm font-medium hover:bg-white/25">
     Go to About sections →
  </a>
</div>

    {{-- Mini chart: Orders last 7 days --}}
    <x-card class="lg:col-span-2">
      <div class="flex items-center justify-between">
        <h3 class="font-semibold">Orders — last 7 days</h3>
        <span class="text-xs text-gray-500 dark:text-gray-400">Bars scale to your data</span>
      </div>

      <div class="mt-4">
        <div class="h-28 w-full rounded bg-gray-50 dark:bg-gray-900/60 p-3">
          <div class="flex items-end gap-2 h-full">
            @foreach($ordersPerDay as $v)
              @php
                $h = max(6, intval(($v / $ordersMax) * 100)); // percent height
                $label = $v.' orders';
              @endphp
              <div class="flex-1 relative">
                <div class="mx-auto w-6 rounded-t bg-[var(--accent)]"
                     style="height: {{ $h }}%;"></div>
                <div class="absolute inset-x-0 -bottom-6 text-center text-[11px] text-gray-500 dark:text-gray-400">{{ $v }}</div>
              </div>
            @endforeach
          </div>
        </div>
      </div>
    </x-card>

    {{-- Mini chart: Content mix --}}
    <x-card class="lg:col-span-1">
      <div class="flex items-center justify-between">
        <h3 class="font-semibold">Content mix</h3>
        <span class="text-xs text-gray-500 dark:text-gray-400">Share by type</span>
      </div>

      <div class="mt-4 space-y-3">
        @foreach($contentMix as $row)
          @php
            $pct = round(($row['value'] / $contentTotal) * 100);
          @endphp
          <div>
            <div class="flex justify-between text-xs mb-1">
              <span class="text-gray-600 dark:text-gray-300">{{ $row['label'] }}</span>
              <span class="text-gray-500 dark:text-gray-400">{{ $row['value'] }} ({{ $pct }}%)</span>
            </div>
            <div class="h-2 rounded bg-gray-100 dark:bg-gray-900/60 overflow-hidden">
              <div class="h-2" style="width: {{ $pct }}%; background: {{ $row['color'] }};"></div>
            </div>
          </div>
        @endforeach
      </div>
    </x-card>
  </div>
</x-admin-layout>