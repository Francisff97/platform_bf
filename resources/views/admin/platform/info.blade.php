{{-- resources/views/admin/platform/info.blade.php --}}
<x-admin-layout title="Platform info">
  @php
    $title   = $info['title']   ?? 'Base Forge – Platform information';
    $intro   = $info['intro']   ?? null;
    $note    = $info['note']    ?? null;
    $ver     = $info['version'] ?? null;
    $meta    = $info['_meta']   ?? [];
    $source  = $meta['source']  ?? null;

    $dev     = $info['developer'] ?? [];
    $addons  = $info['addons']    ?? [];

    // piccole utility
    $btnCls  = 'inline-flex items-center gap-2 rounded-full px-3 py-1.5 text-sm font-medium text-white hover:opacity-90';
    $pillCls = 'rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-semibold text-gray-700 dark:bg-gray-800 dark:text-gray-100';
  @endphp

  {{-- Header --}}
  <div class="mb-6 flex items-start justify-between">
    <div>
      <h1 class="font-orbitron text-2xl text-gray-900 dark:text-gray-100">{{ $title }}</h1>
      @if($intro)
        <p class="mt-2 max-w-3xl text-sm text-gray-600 dark:text-gray-300">{{ $intro }}</p>
      @endif
      @if($note)
        <p class="mt-2 max-w-3xl text-sm text-gray-600 dark:text-gray-300">{{ $note }}</p>
      @endif
    </div>

    <div class="flex flex-col items-end gap-2">
      @if($ver)
        <span class="{{ $pillCls }}">v. {{ $ver }}</span>
      @endif
      <form method="POST" action="{{ route('admin.platform.info.refresh') }}">
  @csrf
  <button class="rounded border px-3 py-1.5 text-xs hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800">
    Refresh
  </button>
</form>
      @if(session('success'))
        <span class="rounded bg-emerald-100 px-2 py-0.5 text-xs font-semibold text-emerald-800 dark:bg-emerald-900 dark:text-emerald-100">
          {{ session('success') }}
        </span>
      @endif
    </div>
  </div>

  {{-- Developer card --}}
  <div class="mb-6 rounded-2xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-900">
    <div class="flex flex-col items-start gap-4 sm:flex-row sm:items-center sm:justify-between">
      <div class="flex items-center gap-3">
        <img src="{{ $dev['avatar_url'] ?? 'https://www.gravatar.com/avatar/?d=identicon' }}"
             class="h-12 w-12 rounded-full object-cover ring-2 ring-black/5" alt="Avatar">
        <div>
          <div class="font-semibold text-gray-900 dark:text-gray-100">{{ $dev['name'] ?? '—' }}</div>
          <div class="text-xs text-gray-500 dark:text-gray-400">{{ $dev['role'] ?? '' }}</div>
        </div>
      </div>

      <div class="flex flex-wrap items-center gap-2">
        @if(!empty($dev['discord']))
          <a href="{{ $dev['discord'] }}" target="_blank" class="{{ $btnCls }}" style="background: var(--accent);">
            {{-- discord icon --}}
            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M20.3 4.4A18 18 0 0 0 16.6 3c-.2.4-.4.9-.6 1.2a16 16 0 0 0-3.9 0C11.9 3.9 11.6 3.4 11.4 3a18 18 0 0 0-3.8 1.4C4.7 7.2 4.1 11 4.2 14.7a18 18 0 0 0 4 2c.3-.4.6-.9.8-1.4-.5-.2-.9-.4-1.3-.6l.3-.2a12.9 12.9 0 0 0 9.9 0l.3.2c-.4.2-.8.5-1.3.6.2.5.5 1 .8 1.4a18 18 0 0 0 4-2c.2-4.6-.8-8.3-2.9-10.3Z"/></svg>
            Contact me (Discord)
          </a>
        @endif
        @if(!empty($dev['twitter']))
          <a href="{{ $dev['twitter'] }}" target="_blank" class="rounded-full border px-3 py-1.5 text-sm hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800">
            X / Twitter
          </a>
        @endif
        @if(!empty($dev['email']))
          <a href="{{ $dev['email'] }}" class="rounded-full border px-3 py-1.5 text-sm hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800">
            Email
          </a>
        @endif
      </div>
    </div>
  </div>

  {{-- Add-ons --}}
  @if(count($addons))
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
      @foreach($addons as $ad)
        <div class="group overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm ring-1 ring-black/5 transition hover:shadow-md dark:border-gray-700 dark:bg-gray-900">
          <div class="aspect-[16/9] w-full bg-gray-100 dark:bg-gray-800"
               @if(!empty($ad['image']))
                 style="background-image:url('{{ $ad['image'] }}');background-size:cover;background-position:center;"
               @endif></div>

          <div class="p-4">
            <div class="mb-1 font-semibold text-gray-900 dark:text-gray-100">{{ $ad['title'] ?? '—' }}</div>
            @if(!empty($ad['subtitle']))
              <div class="mb-3 text-sm text-gray-600 dark:text-gray-300">{{ $ad['subtitle'] }}</div>
            @endif

            <div class="flex items-center justify-between">
              <a href="{{ $ad['cta_url'] ?? '#' }}" target="_blank" class="{{ $btnCls }}" style="background: var(--accent);">
                {{ $ad['cta_label'] ?? 'Get the add-on' }}
              </a>
              @if(isset($ad['price_eur']))
                <div class="font-orbitron text-lg font-extrabold text-gray-900 dark:text-gray-100">
                  {{ number_format($ad['price_eur'], 0) }}€
                </div>
              @endif
            </div>
          </div>
        </div>
      @endforeach
    </div>
  @else
    <div class="rounded-2xl border border-dashed border-gray-300 p-10 text-center text-sm text-gray-600 dark:border-gray-700 dark:text-gray-300">
      No add-ons available.
    </div>
  @endif

  {{-- CTA viola full width (custom requests) --}}
  <div class="mt-8 overflow-hidden rounded-2xl" style="background: var(--accent);">
    <div class="p-6 sm:p-8">
      <div class="flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center">
        <div>
          <h3 class="font-orbitron text-xl font-semibold text-white">Need something custom?</h3>
          <p class="mt-1 text-sm text-white/90">Request custom features, integrations or design work for your Base Forge installation.</p>
        </div>
        <div class="flex items-center gap-2">
          @if(!empty($dev['discord']))
            <a href="{{ $dev['discord'] }}" target="_blank" class="rounded-full bg-white/15 px-4 py-2 text-sm font-semibold text-white hover:bg-white/25">
              Contact on Discord
            </a>
          @endif
          @if(!empty($dev['email']))
            <a href="{{ $dev['email'] }}" class="rounded-full bg-white px-4 py-2 text-sm font-semibold text-[var(--accent)] hover:opacity-90">
              Email request
            </a>
          @endif
        </div>
      </div>
    </div>
  </div>
</x-admin-layout>