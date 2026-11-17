@props(['title' => 'Auth'])

<!doctype html>
<html lang="en" class="{{ session('theme','')==='dark' ? 'dark' : '' }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>{{ $title }}</title>
  @vite(['resources/css/app.css','resources/js/app.js'])
  <style>
    :root{
      --accent: {{ optional(\App\Models\SiteSetting::first())->color_accent ?? '#4f46e5' }};
    }
  </style>
</head>
<body class="min-h-screen bg-[var(--bg-light,#f7fafc)] text-gray-900
             dark:bg-[var(--bg-dark,#0b0f1a)] dark:text-gray-100 flex flex-col">
  <header class="px-5 py-4">
    <div class="mx-auto max-w-7xl flex items-center justify-between">
      <a href="{{ route('home') }}" class="flex items-center gap-2">
        @php $s = \App\Models\SiteSetting::first(); @endphp
        @if($s?->logo_light_path || $s?->logo_dark_path)
          <img src="{{ $s?->logo_light_path ? Storage::url($s->logo_light_path) : '' }}" class="h-8 w-auto dark:hidden" alt="Logo">
          <img src="{{ $s?->logo_dark_path ? Storage::url($s->logo_dark_path) : '' }}" class="hidden h-8 w-auto dark:block" alt="Logo Dark">
        @else
          <span class="font-semibold tracking-wide">Base Forge</span>
        @endif
      </a>
      <a href="{{ route('home') }}"
         class="rounded-full border px-3 py-1.5 text-sm backdrop-blur
                border-black/10 bg-white/30 hover:bg-white/50
                dark:border-white/10 dark:bg-white/10 dark:hover:bg-white/20">
        Back to site
      </a>
    </div>
  </header>

  <main class="flex-1">
    <div class="mx-auto max-w-7xl px-4">
      <div class="mx-auto max-w-[440px]">
        <div class="relative rounded-2xl border px-6 py-6 shadow-xl backdrop-blur-xl
                    border-black/10 bg-white/40
                    dark:border-white/10 dark:bg-white/5">
          {{-- glow --}}
          <div class="pointer-events-none absolute -inset-1 rounded-2xl opacity-30 blur-2xl"
               style="background: radial-gradient(120px 80px at 20% 0, var(--accent), transparent 60%);"></div>

          {{ $slot }}
        </div>

        {{ $extra ?? '' }}
      </div>
    </div>
  </main>

  <footer class="px-5 py-6 text-center text-xs opacity-70">
    © {{ date('Y') }} Base Forge. All rights reserved.
  </footer>
</body>
</html>