<x-app-layout>
<x-auto-hero />


  <div class="mt-[100px] grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3 ">
    @foreach($builders as $b)
      <a href="{{ route('builders.show',$b->slug) }}" class="group neon-card neon-cyan overflow-hidden dark:bg-gray-900 p-4 rounded-[16px]">
        @if($b->image_path)
          <img src="{{ asset('storage/'.$b->image_path) }}" class="mb-3 h-40 w-full rounded-lg object-cover group-hover:opacity-95" alt="{{ $b->name }}">
        @endif
        <div class="flex items-center justify-between">
          <h3 class="font-semibold text-gray-900 dark:text-gray-100">{{ $b->name }}</h3>
          @if($b->team)<span class="text-xs text-gray-500 dark:text-gray-400">{{ $b->team }}</span>@endif
        </div>
        @if($b->skills)
          <div class="mt-2 flex flex-wrap gap-2">
            @foreach($b->skills as $s)
              <span class="badge badge-sky">{{ $s }}</span>
            @endforeach
          </div>
        @endif
      </a>
    @endforeach
  </div>

  <div class="mt-6">{{ $builders->links() }}</div>
</x-app-layout>
