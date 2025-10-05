<x-app-layout>
  
<x-auto-hero />


  <div class="mt-[100px] grid grid-cols-1 gap-4 md:grid-cols-3">
    @foreach($services as $s)
      <div class="rounded-xl bg-white dark:bg-gray-900 p-5 shadow-sm">
      @if($s->image_path)
    <img src="{{ Storage::url($s->image_path) }}" class="w-full mb-4 aspect-[16/9] object-cover" alt="{{ $s->title }}">
  @endif
        <h3 class="font-semibold">{{ $s->name }}</h3>
        <p class="text-sm text-[var(--accent)]">{{ $s->excerpt }}</p>
      </div>
    @endforeach
  </div>
</x-app-layout>
