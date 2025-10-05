<x-admin-layout title="Edit Hero">
  <form method="POST" action="{{ route('admin.heroes.update',$hero) }}" enctype="multipart/form-data" class="grid max-w-2xl gap-4">
    @csrf @method('PUT')
    <div class="rounded border p-2 bg-gray-50 dark:bg-gray-900">slug: <strong>{{ $hero->page }}</strong></div>
    <input name="title" class="rounded border p-2" value="{{ old('title',$hero->title) }}">
    <input name="subtitle" class="rounded border p-2" value="{{ old('subtitle',$hero->subtitle) }}">
    <input type="file" name="image" accept="image/*" class="rounded border p-2">
    @if($hero->image_path)<img src="{{ Storage::url($hero->image_path) }}" class="h-24 rounded">@endif
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
  <option value="">— No items selected —</option>
  @foreach($pages as $key => $label)
    <option value="{{ $key }}" @selected(old('page', $hero->page ?? '') === $key)>{{ $label }}</option>
  @endforeach
</select>

    <input name="overlay" class="rounded border p-2" value="{{ old('overlay',$hero->overlay) }}">
    <label class="inline-flex items-center gap-2">
      <input type="checkbox" name="is_active" value="1" {{ $hero->is_active ? 'checked':'' }}> Attivo
    </label>
    <button class="rounded bg-[var(--accent)] px-4 py-2 text-white">Aggiorna</button>
  </form>
</x-admin-layout>
