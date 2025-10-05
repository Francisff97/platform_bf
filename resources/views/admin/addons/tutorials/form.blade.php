<x-admin-layout :title="$mode==='create' ? 'New Tutorial' : 'Edit Tutorial'">
  <form method="POST" action="{{ $mode==='create'
        ? route('admin.addons.tutorials.store')
        : route('admin.addons.tutorials.update', $tutorial) }}">
    @csrf
    @if($mode==='edit') @method('PUT') @endif

    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
      <div>
        <x-input-label value="Title"/>
        <input name="title" value="{{ old('title',$tutorial->title) }}" class="mt-1 w-full rounded border px-3 py-2 dark:bg-gray-900 dark:border-gray-700">
        <x-input-error :messages="$errors->get('title')" class="mt-1"/>
      </div>

      <div>
        <x-input-label value="Provider"/>
        <select name="provider" class="mt-1 w-full rounded border px-3 py-2 dark:bg-gray-900 dark:border-gray-700">
          @php $pr = old('provider',$tutorial->provider); @endphp
          <option value="">Auto</option>
          <option value="youtube" @selected($pr==='youtube')>YouTube</option>
          <option value="vimeo"   @selected($pr==='vimeo')>Vimeo</option>
          <option value="url"     @selected($pr==='url')>Direct URL</option>
        </select>
      </div>

      <div class="md:col-span-2">
        <x-input-label value="Video URL"/>
        <input name="video_url" value="{{ old('video_url',$tutorial->video_url) }}" class="mt-1 w-full rounded border px-3 py-2 dark:bg-gray-900 dark:border-gray-700">
        <x-input-error :messages="$errors->get('video_url')" class="mt-1"/>
      </div>

      <div>
        <x-input-label value="Visibility"/>
        @php $pub = old('is_public', $tutorial->is_public); @endphp
        <label class="mt-2 inline-flex items-center gap-2 text-sm">
          <input type="checkbox" name="is_public" value="1" @checked((bool)$pub)>
          Public (unticked = only buyers)
        </label>
      </div>

      <div>
        <x-input-label value="Sort order"/>
        <input type="number" name="sort_order" min="0" value="{{ old('sort_order',$tutorial->sort_order ?? 0) }}" class="mt-1 w-full rounded border px-3 py-2 dark:bg-gray-900 dark:border-gray-700">
      </div>

      <div>
        <x-input-label value="Target Type"/>
        @php
          $isPack = old('target_type', ($tutorial->tutorialable_type ?? '') === \App\Models\Pack::class ? 'pack' : 'coach');
        @endphp
        <select name="target_type" class="mt-1 w-full rounded border px-3 py-2 dark:bg-gray-900 dark:border-gray-700">
          <option value="pack"  @selected($isPack==='pack')>Pack</option>
          <option value="coach" @selected($isPack==='coach')>Coach</option>
        </select>
      </div>

      <div>
        <x-input-label value="Target"/>
        @php
          $targetId = old('target_id', $tutorial->tutorialable_id ?? null);
        @endphp
        <select name="target_id" class="mt-1 w-full rounded border px-3 py-2 dark:bg-gray-900 dark:border-gray-700">
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
      </div>
    </div>

    <div class="mt-6">
      <button class="rounded bg-[var(--accent)] px-4 py-2 text-white">
        {{ $mode==='create' ? 'Create' : 'Save changes' }}
      </button>
      <a href="{{ route('admin.addons.tutorials') }}" class="ml-3 text-sm underline">Cancel</a>
    </div>
  </form>
</x-admin-layout>