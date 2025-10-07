<x-app-layout>
<x-slot name="header"><h1 class="text-2xl font-bold">{{ $builder->name }}</h1></x-slot>

<div class="grid grid-cols-1 md:grid-cols-2 gap-[30px]">
      {{-- avatar/immagine --}}
      @if($builder->image_path)
        <img src="{{ Storage::url($builder->image_path) }}" class="rounded-xl object-cover w-full">
      @endif

      <div class="mt-4 text-sm text-gray-600 dark:text-gray-300">
        <div><span class="font-medium">Team:</span> {{ $builder->team ?? '—' }}</div>
        @if($builder->skills)
          <div class="mt-2"><span class="font-medium">Skills:</span>
            @foreach($builder->skills as $s)
              <span class="ml-2 rounded-full bg-gray-100 px-2 py-0.5 text-xs dark:bg-gray-800">{{ $s }}</span>
            @endforeach
          </div>
        @endif
          <div class="md:col-span-2">
      @if($builder->description)
        <div class="prose max-w-none dark:prose-invert">
          {!! nl2br(e($builder->description)) !!}
        </div>
      @endif
      </div>
    </div>
</div>

  <h2 class="mb-3 mt-[50px] text-lg font-semibold">Packs di {{ $builder->name }}</h2>
  <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
    @foreach($packs as $p)
      <x-pack-card :pack="$p" :badgeColor="($p->category->color ?? 'indigo')" />
    @endforeach
  </div>

  <div class="mt-6">{{ $packs->links() }}</div>
</x-app-layout>
