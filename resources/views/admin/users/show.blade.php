<x-admin-layout title="User details">
  @if(session('ok'))
    <div class="mb-4 rounded-xl border border-emerald-300 bg-emerald-50 px-3 py-2 text-sm text-emerald-800 dark:border-emerald-900/40 dark:bg-emerald-900/20 dark:text-emerald-100">
      {{ session('ok') }}
    </div>
  @endif

  <div class="grid gap-6 sm:grid-cols-2">
    {{-- Identity --}}
    <div class="rounded-3xl border border-gray-200 p-5 shadow-sm ring-1 ring-black/5 dark:border-gray-700 dark:bg-gray-900">
      <div class="mb-4 flex items-center justify-between">
        <h2 class="font-semibold text-gray-900 dark:text-gray-100">Identity</h2>
        <a class="rounded-xl border px-3 py-1.5 text-sm transition hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800"
           href="{{ route('admin.users.edit',$user) }}">Edit</a>
      </div>

      <div class="space-y-3">
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
    </div>

    {{-- Ownership --}}
    <div class="rounded-3xl border border-gray-200 p-5 shadow-sm ring-1 ring-black/5 dark:border-gray-700 dark:bg-gray-900">
      <h2 class="mb-4 font-semibold text-gray-900 dark:text-gray-100">Ownership</h2>

      <div class="mb-3">
        <div class="text-xs font-medium uppercase text-gray-500">Packs</div>
        <div class="mt-2 flex flex-wrap gap-2">
          @forelse($packs as $p)
            <span class="rounded-full border px-2 py-0.5 text-xs dark:border-gray-700
              {{ in_array($p->id,$ownedPacks) ? 'bg-[var(--accent)]/10 border-[var(--accent)] text-[var(--accent)]' : 'opacity-60' }}">
              {{ $p->title }}
            </span>
          @empty
            <span class="text-xs text-gray-500">No packs</span>
          @endforelse
        </div>
      </div>

      <div>
        <div class="text-xs font-medium uppercase text-gray-500">Coaches</div>
        <div class="mt-2 flex flex-wrap gap-2">
          @forelse($coaches as $c)
            <span class="rounded-full border px-2 py-0.5 text-xs dark:border-gray-700
              {{ in_array($c->id,$ownedCoaches) ? 'bg-[var(--accent)]/10 border-[var(--accent)] text-[var(--accent)]' : 'opacity-60' }}">
              {{ $c->name }}
            </span>
          @empty
            <span class="text-xs text-gray-500">No coaches</span>
          @endforelse
        </div>
      </div>
    </div>
  </div>

  {{-- inline component for copyable field --}}
  @once
    @push('components')
    @endpush
  @endonce

  @php /** @var string $label @var string $value */ @endphp
  @props(['label'=>'','value'=>''])
  <div class="mb-3 flex items-center justify-between gap-3">
    <div>
      <div class="text-xs uppercase text-gray-500">{{ $label }}</div>
      <div class="font-medium text-gray-900 dark:text-gray-100">{{ $value }}</div>
    </div>
    <button type="button" class="rounded-lg border px-2 py-1 text-xs transition hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800"
            data-copy="{{ $value }}">Copy</button>
  </div>

  @push('scripts')
  <script>
    document.addEventListener('click',(e)=>{
      if(e.target.matches('[data-copy]')){
        const v = e.target.getAttribute('data-copy') || '';
        navigator.clipboard?.writeText(v);
        e.target.innerText = 'Copied';
        setTimeout(()=>{ e.target.innerText = 'Copy'; }, 900);
      }
    });
  </script>
  @endpush
</x-admin-layout>