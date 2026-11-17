<x-admin-layout title="Appearance">
  @php
    $s = $s ?? \App\Models\SiteSetting::first() ?? new \App\Models\SiteSetting();
    $light = old('color_light_bg', $s->color_light_bg ?? '#f8fafc');
    $dark  = old('color_dark_bg',  $s->color_dark_bg  ?? '#0b0f1a');
    $acc   = old('color_accent',   $s->color_accent   ?? '#4f46e5');
  @endphp

  {{-- Alerts --}}
  @if ($errors->any())
    <div class="mb-4 rounded-xl border border-red-300 bg-red-50/80 px-3 py-2 text-sm text-red-700">
      <div class="font-semibold mb-1">Please fix the following errors:</div>
      <ul class="list-disc pl-5">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
  @endif
  @if (session('success'))
    <div class="mb-4 rounded-xl border border-green-300 bg-green-50/80 px-3 py-2 text-sm text-green-800">
      {{ session('success') }}
    </div>
  @endif

  <form method="POST" action="{{ route('admin.appearance.update') }}" enctype="multipart/form-data" class="grid gap-6">
    @csrf

    {{-- Intro / Tips --}}
    <div class="rounded-2xl border bg-white/70 p-4 text-sm text-gray-700 shadow-sm dark:border-gray-800 dark:bg-gray-900/60 dark:text-gray-100">
      Customize your brand: upload logos, pick your colors and set currency & server link.
    </div>

    {{-- Header: live preview + theme controls (stacked on mobile) --}}
    <div class="grid items-start gap-6 lg:grid-cols-2">

      {{-- LIVE PREVIEW CARD --}}
      <div class="rounded-2xl border bg-white/80 p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900/60">
        <div class="mb-4 flex items-center justify-between">
          <div class="text-sm font-semibold">Live Preview</div>
          <div class="flex items-center gap-2">
            <span class="text-xs text-gray-500">Light</span>
            <label class="relative inline-flex cursor-pointer items-center">
              <input id="previewMode" type="checkbox" class="peer sr-only">
              <div class="h-5 w-10 rounded-full bg-gray-300 peer-checked:bg-gray-600"></div>
              <span class="absolute left-1 top-1 h-3.5 w-3.5 rounded-full bg-white transition peer-checked:translate-x-5"></span>
            </label>
            <span class="text-xs text-gray-500">Dark</span>
          </div>
        </div>

        <div id="previewSurface" class="rounded-xl border p-4 transition-colors dark:border-gray-800"
             style="background: {{ $light }};">

          {{-- top bar preview --}}
          <div id="previewTopbar"
               class="mb-4 flex items-center justify-between rounded-lg border px-3 py-2"
               style="background: #ffffff; border-color: rgba(0,0,0,.08);">
            <div class="flex items-center gap-2">
              @if($s?->logo_light_path)
                <img src="{{ Storage::url($s->logo_light_path) }}" class="h-6 w-auto" alt="">
              @else
                <div class="h-6 w-20 rounded bg-gray-200"></div>
              @endif
              <span class="hidden text-xs text-gray-500 sm:inline">Navbar</span>
            </div>
            <button id="previewCta" type="button" class="rounded px-3 py-1.5 text-white text-xs"
                    style="background: {{ $acc }};">Call to action</button>
          </div>

          {{-- content preview --}}
          <div id="previewCard" class="rounded-xl p-4 shadow-sm"
               style="background:#ffffff;border:1px solid rgba(0,0,0,.08);">
            <div class="mb-2 h-4 w-28 rounded" id="previewTitle" style="background: {{ $acc }};"></div>
            <div class="space-y-1">
              <div class="h-2 w-full rounded bg-gray-200/90"></div>
              <div class="h-2 w-5/6 rounded bg-gray-200/90"></div>
              <div class="h-2 w-4/6 rounded bg-gray-200/90"></div>
            </div>
          </div>
        </div>
      </div>

      {{-- THEME CONTROLS --}}
      <div class="grid gap-6">
        {{-- Logos uploader --}}
        <div class="rounded-2xl border bg-white/80 p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900/60">
          <div class="mb-3 text-sm font-semibold">Logos</div>
          <div class="grid gap-5 sm:grid-cols-2">
            <label class="block">
              <div class="mb-1 text-xs font-medium uppercase tracking-wide">Light logo</div>
              <div class="flex items-center gap-3">
                <div class="h-12 w-28 overflow-hidden rounded-lg ring-1 ring-black/5 dark:ring-white/10 bg-white">
                  <img id="logoLightPreview"
                       src="{{ $s->logo_light_path ? Storage::url($s->logo_light_path) : '' }}"
                       class="h-full w-full object-contain {{ $s->logo_light_path ? '' : 'hidden' }}">
                </div>
                <label class="inline-flex cursor-pointer items-center gap-2 rounded-lg border px-3 py-2 text-sm dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800">
                  Upload
                  <input id="logoLightInput" type="file" name="logo_light" accept="image/*" class="hidden">
                </label>
              </div>
            </label>

            <label class="block">
              <div class="mb-1 text-xs font-medium uppercase tracking-wide">Dark logo</div>
              <div class="flex items-center gap-3">
                <div class="h-12 w-28 overflow-hidden rounded-lg ring-1 ring-black/5 dark:ring-white/10" style="background:#0b0f1a">
                  <img id="logoDarkPreview"
                       src="{{ $s->logo_dark_path ? Storage::url($s->logo_dark_path) : '' }}"
                       class="h-full w-full object-contain {{ $s->logo_dark_path ? '' : 'hidden' }}">
                </div>
                <label class="inline-flex cursor-pointer items-center gap-2 rounded-lg border px-3 py-2 text-sm dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800">
                  Upload
                  <input id="logoDarkInput" type="file" name="logo_dark" accept="image/*" class="hidden">
                </label>
              </div>
            </label>
          </div>
        </div>
        <x-admin.image-hint field="logo"/>
        {{-- Colors --}}
        <div class="rounded-2xl border bg-white/80 p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900/60">
          <div class="mb-3 text-sm font-semibold">Colors</div>
          <div class="grid gap-4 sm:grid-cols-3">
            <label class="block">
              <div class="mb-1 text-xs uppercase tracking-wide text-gray-500">Light background</div>
              <input id="colorLight" type="color" name="color_light_bg" value="{{ $light }}"
                     class="h-11 w-full rounded-lg border dark:border-gray-700">
            </label>
            <label class="block">
              <div class="mb-1 text-xs uppercase tracking-wide text-gray-500">Dark background</div>
              <input id="colorDark" type="color" name="color_dark_bg" value="{{ $dark }}"
                     class="h-11 w-full rounded-lg border dark:border-gray-700">
            </label>
            <label class="block">
              <div class="mb-1 text-xs uppercase tracking-wide text-gray-500">Accent</div>
              <input id="colorAcc" type="color" name="color_accent" value="{{ $acc }}"
                     class="h-11 w-full rounded-lg border dark:border-gray-700">
            </label>
          </div>
          <div class="mt-3 flex items-center gap-3 text-xs text-gray-500">
            <span class="inline-flex items-center gap-2"><span class="h-4 w-4 rounded" style="background: {{ $light }}"></span>Light</span>
            <span class="inline-flex items-center gap-2"><span class="h-4 w-4 rounded" style="background: {{ $dark }}"></span>Dark</span>
            <span class="inline-flex items-center gap-2"><span class="h-4 w-4 rounded" style="background: {{ $acc }}"></span>Accent</span>
          </div>
        </div>
      </div>
    </div>

    {{-- Server Link --}}
    <div class="rounded-2xl border bg-white/80 p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900/60">
      <div class="mb-3 text-sm font-semibold">Server Link</div>
      <label class="block">
        <input type="url" name="discord_url"
               value="{{ old('discord_url', $s?->discord_url) }}"
               placeholder="https://discord.gg/xxxxxx"
               class="w-full rounded-lg border px-3 py-2 dark:bg-gray-900 dark:border-gray-700">
      </label>
      <p class="mt-1 text-xs text-gray-500">Full URL (e.g. https://discord.gg/xxxxxx)</p>
    </div>

    {{-- Currency --}}
    <div class="rounded-2xl border bg-white/80 p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900/60">
      <div class="mb-3 text-sm font-semibold">Currency & FX</div>
      <div class="grid gap-4 sm:grid-cols-2">
        <label class="block">
          <div class="mb-1 text-xs uppercase tracking-wide text-gray-500">Store currency</div>
          @php $curr = old('currency', $s->currency ?? 'EUR'); @endphp
          <select name="currency" class="w-full rounded-lg border px-3 py-2 dark:bg-gray-900 dark:border-gray-700">
            <option value="EUR" @selected($curr==='EUR')>EUR</option>
            <option value="USD" @selected($curr==='USD')>USD</option>
          </select>
          <p class="mt-1 text-xs text-gray-500">Used for prices & PayPal orders.</p>
        </label>

        <label class="block">
          <div class="mb-1 text-xs uppercase tracking-wide text-gray-500">USD per 1 EUR</div>
          <input type="number" step="0.000001" min="0.000001" name="fx_usd_per_eur"
                 value="{{ old('fx_usd_per_eur', $s->fx_usd_per_eur ?? 1.08) }}"
                 class="w-full rounded-lg border px-3 py-2 dark:bg-gray-900 dark:border-gray-700" />
          <p class="mt-1 text-xs text-gray-500">Example: 1.08 → €1 = $1.08</p>
        </label>
      </div>
    </div>

    <div class="flex items-center gap-3">
      <button class="rounded-lg bg-[var(--accent)] px-4 py-2 text-white hover:opacity-90">Save</button>
      <a href="{{ route('admin.dashboard') }}" class="text-sm underline">Cancel</a>
    </div>
  </form>

  {{-- Tiny JS: previews live (no dipendenze) --}}
  <script>
    (function () {
      const el = (id)=>document.getElementById(id);

      // Color bindings
      const light = el('colorLight'), dark = el('colorDark'), acc = el('colorAcc');
      const surface = el('previewSurface'), topbar = el('previewTopbar'), cta = el('previewCta'), title = el('previewTitle');
      const mode = el('previewMode');

      function apply() {
        const isDark = mode?.checked;
        const bg = isDark ? (dark?.value || '{{ $dark }}') : (light?.value || '{{ $light }}');
        surface && (surface.style.background = bg);
        cta && (cta.style.background = (acc?.value || '{{ $acc }}'));
        title && (title.style.background = (acc?.value || '{{ $acc }}'));
        // topbar contrast
        if (isDark) {
          topbar && (topbar.style.background = '#111827', topbar.style.borderColor = 'rgba(255,255,255,.08)');
        } else {
          topbar && (topbar.style.background = '#ffffff', topbar.style.borderColor = 'rgba(0,0,0,.08)');
        }
      }
      [light, dark, acc, mode].forEach(i => i && i.addEventListener('input', apply));
      apply();

      // Logo previews
      const lightInput = document.getElementById('logoLightInput');
      const darkInput  = document.getElementById('logoDarkInput');
      const lightPrev  = document.getElementById('logoLightPreview');
      const darkPrev   = document.getElementById('logoDarkPreview');

      function preview(input, img) {
        const [file] = input.files || [];
        if (!file) return;
        const url = URL.createObjectURL(file);
        img.src = url;
        img.classList.remove('hidden');
      }
      lightInput?.addEventListener('change', ()=>preview(lightInput, lightPrev));
      darkInput?.addEventListener('change', ()=>preview(darkInput, darkPrev));
    })();
  </script>
</x-admin-layout>