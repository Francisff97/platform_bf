{{-- resources/views/admin/appearance/edit.blade.php --}}
<x-admin-layout title="Appearance">
  @php
    $s = $s ?? \App\Models\SiteSetting::first() ?? new \App\Models\SiteSetting();
  @endphp

  @if ($errors->any())
    <div class="mb-4 rounded border border-red-300 bg-red-50 px-3 py-2 text-sm text-red-700">
      <div class="font-semibold mb-1">Please fix the following errors:</div>
      <ul class="list-disc pl-5">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
  @endif

  @if (session('success'))
    <div class="mb-4 rounded border border-green-300 bg-green-50 px-3 py-2 text-sm text-green-800">
      {{ session('success') }}
    </div>
  @endif

  <form method="POST" action="{{ route('admin.appearance.update') }}" enctype="multipart/form-data" class="grid max-w-4xl gap-6">
    @csrf

    <div class="bg-gray-50 p-4 rounded border border-gray-200 dark:bg-gray-800 dark:border-gray-700 text-sm text-gray-600 dark:text-white">
      Customize your site look & feel, currency, and server link.
    </div>

    {{-- Logos --}}
    <div class="grid md:grid-cols-2 gap-6">
      <div>
        <label class="block text-sm mb-1">Light Logo</label>
        <input type="file" name="logo_light" accept="image/*" class="w-full rounded border p-2">
        @if(!empty($s->logo_light_path))
          <img src="{{ Storage::url($s->logo_light_path) }}" class="mt-2 h-12" alt="Light Logo">
        @endif
      </div>
      <div>
        <label class="block text-sm mb-1">Dark Logo</label>
        <input type="file" name="logo_dark" accept="image/*" class="w-full rounded border p-2">
        @if(!empty($s->logo_dark_path))
          <div class="mt-2 rounded bg-black p-2 inline-block">
            <img src="{{ Storage::url($s->logo_dark_path) }}" class="h-12" alt="Dark Logo">
          </div>
        @endif
      </div>
    </div>

    {{-- Colors --}}
    <div class="grid md:grid-cols-3 gap-6">
      <div>
        <label class="block text-sm mb-1">Light Background</label>
        <input type="color" name="color_light_bg" value="{{ old('color_light_bg',$s->color_light_bg ?? '#f8fafc') }}" class="h-10 w-full rounded border">
      </div>
      <div>
        <label class="block text-sm mb-1">Dark Background</label>
        <input type="color" name="color_dark_bg" value="{{ old('color_dark_bg',$s->color_dark_bg ?? '#0b0f1a') }}" class="h-10 w-full rounded border">
      </div>
      <div>
        <label class="block text-sm mb-1">Accent Color</label>
        <input type="color" name="color_accent" value="{{ old('color_accent',$s->color_accent ?? '#4f46e5') }}" class="h-10 w-full rounded border">
      </div>
    </div>

    <div class="rounded border p-4 dark:border-gray-800">
      <div class="text-sm mb-2 font-medium">Quick Preview</div>
      <div class="flex items-center gap-3">
        <span class="h-8 w-8 rounded" style="background: {{ $s->color_light_bg ?? '#f8fafc' }}"></span>
        <span class="h-8 w-8 rounded" style="background: {{ $s->color_dark_bg ?? '#0b0f1a' }}"></span>
        <span class="h-8 w-8 rounded" style="background: {{ $s->color_accent ?? '#4f46e5' }}"></span>
        <button type="button" class="ml-4 rounded px-3 py-1.5 text-white" style="background: {{ $s->color_accent ?? '#4f46e5' }}">Button</button>
      </div>
    </div>

    {{-- Server link (Discord/Web) --}}
    <div>
      <label class="block text-sm text-gray-600 dark:text-white">Server Link</label>
      <input type="url" name="discord_url" class="mt-1 w-full rounded border p-2 dark:bg-gray-900 dark:border-gray-600 dark:text-white"
             value="{{ old('discord_url', $s?->discord_url) }}">
      <p class="mt-1 text-xs text-gray-500 dark:text-white">Full URL (e.g. https://discord.gg/xxxxxx)</p>
    </div>

    {{-- Currency & FX --}}
    <div class="rounded border p-4 dark:border-gray-800">
      <h3 class="mb-3 text-sm font-semibold uppercase text-gray-500">Currency</h3>

      <div class="grid gap-4 sm:grid-cols-2">
        <div>
          <label class="block text-sm font-medium mb-1">Store Currency</label>
          @php $curr = old('currency', $s->currency ?? 'EUR'); @endphp
          <select name="currency" class="w-full rounded border px-3 py-2 dark:bg-gray-900">
            <option value="EUR" @selected($curr==='EUR')>EUR</option>
            <option value="USD" @selected($curr==='USD')>USD</option>
          </select>
          <p class="mt-1 text-xs text-gray-500">Currency used to display prices and create PayPal orders.</p>
        </div>

        <div>
          <label class="block text-sm font-medium mb-1">USD per 1 EUR (FX)</label>
          <input type="number" step="0.000001" min="0.000001" name="fx_usd_per_eur"
                 value="{{ old('fx_usd_per_eur', $s->fx_usd_per_eur ?? 1.08) }}"
                 class="w-full rounded border px-3 py-2 dark:bg-gray-900" />
          <p class="mt-1 text-xs text-gray-500">Example: 1.08 means €1 = $1.08</p>
        </div>
      </div>
    </div>

    <div>
      <button class="rounded bg-[var(--accent)] px-4 py-2 text-white">Save</button>
    </div>
  </form>
</x-admin-layout>
