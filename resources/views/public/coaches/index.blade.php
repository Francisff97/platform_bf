<x-app-layout>
  <x-auto-hero />

  <div class="mt-[100px] grid grid-cols-2 md:grid-cols-4 gap-6">
    @foreach($coaches as $c)
      <a href="{{ route('coaches.show',$c->slug) }}"
         class="rounded-2xl border bg-white p-4 text-center shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <div class="mx-auto mb-2 h-[120px] w-[120px] overflow-hidden rounded-full bg-gray-200 dark:bg-gray-800">
          @if($c->image_path)
            <x-img :src="Storage::url($c->image_path)" class="h-full w-full object-cover" />
          @endif
        </div>
        <div class="font-semibold">{{ $c->name }}</div>
        <div class="text-xs text-gray-500">{{ $c->team ?? '—' }}</div>
      </a>
    @endforeach
  </div>

  <div class="mt-6">{{ $coaches->links() }}</div>
</x-app-layout>
