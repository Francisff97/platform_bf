<x-admin-layout title="Create Hero Section">
  <form method="POST" action="{{ route('admin.heroes.store') }}" enctype="multipart/form-data" class="grid max-w-2xl gap-4">
    @csrf
    <input name="page" class="rounded border p-2" placeholder="es. packs / services / builders / home" required>
    <input name="title" class="rounded border p-2" placeholder="Title">
    <input name="subtitle" class="rounded border p-2" placeholder="Subtitle">
    <input type="file" name="image" accept="image/*" class="rounded border p-2">
    <div class="grid gap-4 md:grid-cols-2">
  <div>
    <label class="block text-sm mb-1">Height (px/vh)</label>
    <input name="height_css" value="{{ old('height_css', $hero->height_css ?? '70vh') }}"
           placeholder="es: 70vh oppure 480px"
           class="w-full rounded border p-2">
    <p class="mt-1 text-xs text-gray-500">Format: <code>70vh</code> o <code>480px</code></p>
    @error('height_css') <div class="text-sm text-red-600">{{ $message }}</div> @enderror
  </div>
  <div class="flex items-end">
    <label class="inline-flex items-center gap-2">
      <input type="checkbox" name="full_bleed" value="1"
             {{ old('full_bleed', $hero->full_bleed ?? true) ? 'checked' : '' }}>
      <span>Full width (edge-to-edge)</span>
    </label>
  </div>
</div>
<label class="block text-sm mb-1">Page</label>
<select name="page" class="w-full rounded border p-2">
@php
    $pages = [
      'home'          => 'Home',
      'packs'         => 'Packs (list)',
      'packs.show'    => 'Pack (detail)',
      'services'      => 'Services (list)',
      'builders'      => 'Builders (list)',
      'builders.show' => 'Builder (detail)',
      'coaches'       => 'Coaches (list)',
      'coaches.show'  => 'Coach (detail)',
      'about'         => 'About',
      'contacts'      => 'Contacts',
    ];

    // Recupera i flag
    $ff = \App\Support\FeatureFlags::all();

    // Aggiungi voci solo se discord è abilitato
    if (!empty($ff['discord_integration'])) {
        $pages['announcements']       = 'Announcements';
        $pages['customers-feedback']  = 'Customers Feedback';
    }
@endphp
  <option value="">— No item selected —</option>
  @foreach($pages as $key => $label)
    <option value="{{ $key }}" @selected(old('page', $hero->page ?? '') === $key)>{{ $label }}</option>
  @endforeach
</select>

    <input name="overlay" class="rounded border p-2" value="from-black/20 via-black/10 to-black/50">
    <label class="inline-flex items-center gap-2"><input type="checkbox" name="is_active" value="1" checked> Active</label>
    <button class="rounded bg-[var(--accent)] px-4 py-2 text-white">Save</button>
  </form>
</x-admin-layout>
