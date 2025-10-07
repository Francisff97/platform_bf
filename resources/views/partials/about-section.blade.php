@php
  $img = $s->image_path ? Storage::url($s->image_path) : null;
@endphp

@if($s->layout === 'hero')
  <section class="rounded-2xl bg-gradient-to-br from-indigo-50 to-white dark:from-gray-800 dark:to-gray-900 border p-8">
    <h2 class="text-2xl font-bold mb-2">{{ $s->title }}</h2>
    @if($s->body)<p class="text-gray-600 dark:text-gray-300 leading-relaxed">{{ $s->body }}</p>@endif
    @if($img)<img src="{{ $img }}" class="mt-6 rounded-xl w-full object-cover" alt="About image">@endif
  </section>
@elseif($s->layout === 'image_left' || $s->layout === 'image_right')
  <section class="grid items-center revert gap-6 md:grid-cols-2">
    @if(($s->layout==='image_left') && $img)
      <img src="{{ $img }}" class="rounded-xl w-full object-cover aspect-[4/3]" alt="">
    @endif

    <div>
      @if($s->title)<h3 class="text-xl font-semibold mb-2">{{ $s->title }}</h3>@endif
      @if($s->body)<div class="prose max-w-none dark:prose-invert">{!! nl2br(e($s->body)) !!}</div>@endif
    </div>
    @if(($s->layout==='image_right') && $img)
      <img src="{{ $img }}" class="rounded-xl w-full object-cover aspect-[4/3]" alt="">
    @endif
  </section>
@else
  <section>
    @if($s->title)<h3 class="text-xl font-semibold mb-2">{{ $s->title }}</h3>@endif
    @if($s->body)<div class="prose max-w-none dark:prose-invert">{!! nl2br(e($s->body)) !!}</div>@endif
  </section>
@endif
