<x-admin-layout :title="$mode==='create' ? 'New Tutorial' : 'Edit Tutorial'">
  <form method="POST"
        action="{{ $mode==='create'
          ? route('admin.addons.tutorials.store')
          : route('admin.addons.tutorials.update', $tutorial) }}"
        class="mx-auto grid max-w-4xl gap-5 rounded-2xl border border-[color:var(--accent)]/30 bg-white/70 p-6 shadow-sm backdrop-blur
               dark:border-[color:var(--accent)]/30 dark:bg-gray-900/70">
    @csrf
    @if($mode==='edit') @method('PUT') @endif

    {{-- Titolo e provider --}}
    <div class="grid gap-4 md:grid-cols-2">
      <label class="block">
        <div class="mb-1 text-sm font-medium">Title</div>
        <input name="title" value="{{ old('title',$tutorial->title) }}"
               class="w-full rounded-xl border border-[color:var(--accent)]/40 px-3 py-2
                      focus:ring-2 focus:ring-[color:var(--accent)]
                      dark:bg-black/70 dark:text-white dark:border-gray-800"/>
      </label>

      <label class="block">
        <div class="mb-1 text-sm font-medium">Provider</div>
        @php $pr = old('provider',$tutorial->provider); @endphp
        <select name="provider"
                class="w-full rounded-xl border border-[color:var(--accent)]/40 px-3 py-2
                       focus:ring-2 focus:ring-[color:var(--accent)]
                       dark:bg-black/70 dark:text-white dark:border-gray-800">
          <option value="">Auto</option>
          <option value="youtube" @selected($pr==='youtube')>YouTube</option>
          <option value="vimeo"   @selected($pr==='vimeo')>Vimeo</option>
          <option value="url"     @selected($pr==='url')>Direct URL</option>
        </select>
      </label>
    </div>

    {{-- URL video --}}
    <label class="block">
      <div class="mb-1 text-sm font-medium">Video URL</div>
      <input name="video_url" value="{{ old('video_url',$tutorial->video_url) }}"
             class="w-full rounded-xl border border-[color:var(--accent)]/40 px-3 py-2
                    focus:ring-2 focus:ring-[color:var(--accent)]
                    dark:bg-black/70 dark:text-white dark:border-gray-800"/>
      <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
        Supported: YouTube, Vimeo, Loom, or direct mp4 link.
      </p>
    </label>

    {{-- Visibilità + Sort --}}
    <div class="grid gap-4 md:grid-cols-2">
      <label class="block">
        <div class="mb-1 text-sm font-medium">Visibility</div>
        @php $pub = old('is_public', $tutorial->is_public); @endphp
        <label class="inline-flex items-center gap-2 text-sm">
          <input type="checkbox" name="is_public" value="1" @checked((bool)$pub)>
          Public (unchecked = buyers only)
        </label>
      </label>

      <label class="block">
        <div class="mb-1 text-sm font-medium">Sort Order</div>
        <input type="number" name="sort_order" min="0"
               value="{{ old('sort_order',$tutorial->sort_order ?? 0) }}"
               class="w-full rounded-xl border border-[color:var(--accent)]/40 px-3 py-2
                      focus:ring-2 focus:ring-[color:var(--accent)]
                      dark:bg-black/70 dark:text-white dark:border-gray-800"/>
      </label>
    </div>

    {{-- Target --}}
    <div class="grid gap-4 md:grid-cols-2">
      <label class="block">
        <div class="mb-1 text-sm font-medium">Target Type</div>
        @php
          $isPack = old('target_type', ($tutorial->tutorialable_type ?? '') === \App\Models\Pack::class ? 'pack' : 'coach');
        @endphp
        <select name="target_type"
                class="w-full rounded-xl border border-[color:var(--accent)]/40 px-3 py-2
                       focus:ring-2 focus:ring-[color:var(--accent)]
                       dark:bg-black/70 dark:text-white dark:border-gray-800">
          <option value="pack"  @selected($isPack==='pack')>Pack</option>
          <option value="coach" @selected($isPack==='coach')>Coach</option>
        </select>
      </label>

      <label class="block">
        <div class="mb-1 text-sm font-medium">Target</div>
        @php $targetId = old('target_id', $tutorial->tutorialable_id ?? null); @endphp
        <select name="target_id"
                class="w-full rounded-xl border border-[color:var(--accent)]/40 px-3 py-2
                       focus:ring-2 focus:ring-[color:var(--accent)]
                       dark:bg-black/70 dark:text-white dark:border-gray-800">
          <optgroup label="Packs">
            @foreach($packs as $p)
              <option value="{{ $p->id }}" @selected($isPack==='pack' && (int)$targetId===$p->id)>{{ $p->title }}</option>
            @endforeach
          </optgroup>
          <optgroup label="Coaches">
            @foreach($coaches as $c)
              <option value="{{ $c->id }}" @selected($isPack!=='pack' && (int)$targetId===$c->id)>{{ $c->name }}</option>
            @endforeach
          </optgroup>
        </select>
      </label>
    </div>

    <div class="mt-4 flex items-center gap-3">
      <button class="inline-flex items-center justify-center rounded-xl bg-[color:var(--accent)] px-5 py-2.5 text-white transition
                     hover:opacity-90 active:opacity-80 focus:outline-none focus-visible:ring-2 focus-visible:ring-white/60">
        {{ $mode==='create' ? 'Create tutorial' : 'Save changes' }}
      </button>
      <a href="{{ route('admin.addons.tutorials') }}" class="text-sm text-gray-600 underline dark:text-gray-300">Cancel</a>
    </div>
  </form>
</x-admin-layout>
