<x-admin-layout title="User details">
  @if(session('ok'))
    <div class="mb-4 rounded border border-emerald-300 bg-emerald-50 px-3 py-2 text-sm text-emerald-800">
      {{ session('ok') }}
    </div>
  @endif

  <div class="grid gap-6 sm:grid-cols-2">
    <div class="rounded border p-4 dark:border-gray-700">
      <div class="mb-4 flex items-center justify-between">
        <h2 class="text-sm font-semibold uppercase text-gray-500">Identity</h2>
        <a class="rounded border px-3 py-1.5 text-sm dark:border-gray-700" href="{{ route('admin.users.edit',$user) }}">Edit</a>
      </div>

      <x-field label="ID" :value="$user->id"/>
      <x-field label="Name" :value="$user->name"/>
      <x-field label="Email" :value="$user->email"/>
      <x-field label="Role" :value="$user->role"/>
      @if(property_exists($user,'is_buyer'))
        <x-field label="Buyer" :value="$user->is_buyer ? 'Yes' : 'No'"/>
      @endif
      <x-field label="Created at" :value="$user->created_at"/>
      <x-field label="Updated at" :value="$user->updated_at"/>
    </div>

    <div class="rounded border p-4 dark:border-gray-700">
      <h2 class="mb-4 text-sm font-semibold uppercase text-gray-500">Ownership</h2>

      <div class="mb-3">
        <div class="text-xs font-medium uppercase text-gray-500">Packs</div>
        <div class="mt-1 flex flex-wrap gap-2">
          @foreach($packs as $p)
            <span class="rounded-full border px-2 py-0.5 text-xs dark:border-gray-700
              {{ in_array($p->id,$ownedPacks) ? 'bg-[var(--accent)]/10 border-[var(--accent)] text-[var(--accent)]' : 'opacity-60' }}">
              {{ $p->title }}
            </span>
          @endforeach
        </div>
      </div>

      <div>
        <div class="text-xs font-medium uppercase text-gray-500">Coaches</div>
        <div class="mt-1 flex flex-wrap gap-2">
          @foreach($coaches as $c)
            <span class="rounded-full border px-2 py-0.5 text-xs dark:border-gray-700
              {{ in_array($c->id,$ownedCoaches) ? 'bg-[var(--accent)]/10 border-[var(--accent)] text-[var(--accent)]' : 'opacity-60' }}">
              {{ $c->name }}
            </span>
          @endforeach
        </div>
      </div>
    </div>
  </div>

  {{-- componentino inline per i campi copiabili --}}
  @push('scripts')
  <script>
    document.addEventListener('click',(e)=>{
      if(e.target.matches('[data-copy]')){
        const v = e.target.getAttribute('data-copy') || '';
        navigator.clipboard?.writeText(v);
        e.target.innerText = 'Copied';
        setTimeout(()=>{ e.target.innerText = 'Copy'; }, 1000);
      }
    });
  </script>
  @endpush
</x-admin-layout>

{{-- component "field" minimal --}}
@once
  @push('components')
  @endpush
@endonce

@php
  // Inline component-like helper
@endphp
@props(['label'=>'','value'=>''])
<div class="mb-3 flex items-center justify-between gap-3">
  <div>
    <div class="text-xs uppercase text-gray-500">{{ $label }}</div>
    <div class="font-medium">{{ $value }}</div>
  </div>
  <button type="button" class="rounded border px-2 py-1 text-xs dark:border-gray-700"
          data-copy="{{ $value }}">Copy</button>
</div>
