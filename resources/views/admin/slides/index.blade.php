<x-admin-layout title="Sliders">
  {{-- Intro invariata --}}
  <div class="mb-6 rounded-xl border border-gray-200 bg-gray-50 p-4 text-sm text-gray-600 shadow-sm
              dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200">
    Welcome to the Slides management page. Here you can create, edit, and delete slides that appear in the homepage slider. Use the "New Slide" button to add a new slide, and click "Edit" next to an existing slide to modify its content or settings.
  </div>

  <div class="mb-4 flex items-center justify-between">
    <h2 class="text-lg font-semibold">Slides</h2>
    <a href="{{ route('admin.slides.create') }}"
       class="rounded-full bg-[var(--accent)] px-3 py-1.5 text-sm text-white hover:opacity-90">
      Add slide
    </a>
  </div>

  {{-- ===== MOBILE: CARD LIST ===== --}}
  <div class="grid grid-cols-1 gap-4 md:hidden">
    @forelse($slides as $s)
      <div class="overflow-hidden rounded-xl border bg-white shadow-sm ring-1 ring-black/5 dark:border-gray-800 dark:bg-gray-900 dark:ring-white/10">
        <div class="flex gap-3 p-3">
          {{-- preview --}}
          <div class="h-16 w-28 overflow-hidden rounded-lg bg-gray-100 ring-1 ring-black/5 dark:bg-gray-800 dark:ring-white/10">
            @if($s->image_path)
              <img src="{{ Storage::url($s->image_path) }}" class="h-full w-full object-cover" alt="">
            @endif
          </div>

          {{-- content --}}
          <div class="min-w-0 flex-1">
            <div class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
              <span>#{{ $s->sort_order }}</span>
              <span>•</span>
              <span class="{{ $s->is_active ? 'text-emerald-600 dark:text-emerald-300' : 'text-gray-500' }}">{{ $s->is_active?'Active':'Hidden' }}</span>
            </div>
            <div class="line-clamp-1 font-medium text-gray-900 dark:text-gray-100">{{ $s->title ?: '—' }}</div>
            @if($s->cta_url)
              <a href="{{ $s->cta_url }}" target="_blank" class="mt-0.5 inline-flex items-center gap-1 text-xs text-[color:var(--accent)] hover:underline">
                {{ $s->cta_label ?: 'Link' }}
                <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M12 5l7 7-7 7M5 12h14"/></svg>
              </a>
            @endif
          </div>
        </div>

        <div class="flex items-center justify-end gap-3 border-t p-3 text-sm dark:border-gray-800">
          <a class="text-indigo-600 hover:underline" href="{{ route('admin.slides.edit',$s) }}">Edit</a>
          <form class="inline" method="POST" action="{{ route('admin.slides.destroy',$s) }}" onsubmit="return confirm('Eliminare?')">
            @csrf @method('DELETE')
            <button class="text-rose-600 hover:underline">Delete</button>
          </form>
        </div>
      </div>
    @empty
      <div class="rounded-xl border bg-white p-4 text-center text-gray-500 shadow-sm dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300">
        Add or create a slide. Empty for now!
      </div>
    @endforelse
  </div>

  {{-- ===== DESKTOP: TABLE ===== --}}
  <div class="hidden overflow-hidden rounded-xl border bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900 md:block">
    <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-800">
      <thead class="bg-gray-50 dark:bg-gray-900">
        <tr>
          <th class="px-4 py-3 text-left">#</th>
          <th class="px-4 py-3 text-left">Preview</th>
          <th class="px-4 py-3 text-left">Title</th>
          <th class="px-4 py-3 text-left">CTA</th>
          <th class="px-4 py-3 text-center">Active</th>
          <th class="px-4 py-3 text-right">Actions</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
        @forelse($slides as $s)
          <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/60">
            <td class="px-4 py-3 align-middle">{{ $s->sort_order }}</td>
            <td class="px-4 py-3 align-middle">
              @if($s->image_path)
                <img src="{{ Storage::url($s->image_path) }}" class="h-12 w-20 rounded object-cover" alt="">
              @else
                <div class="h-12 w-20 rounded bg-gray-200 dark:bg-gray-800"></div>
              @endif
            </td>
            <td class="px-4 py-3 align-middle">{{ $s->title ?? '—' }}</td>
            <td class="px-4 py-3 align-middle">
              @if($s->cta_url)
                <a href="{{ $s->cta_url }}" target="_blank" class="text-indigo-600 hover:underline">
                  {{ $s->cta_label ?? 'Link' }}
                </a>
              @else
                —
              @endif
            </td>
            <td class="px-4 py-3 text-center align-middle">
              <span class="rounded-full px-2.5 py-0.5 text-xs font-medium
                {{ $s->is_active ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-200' : 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300' }}">
                {{ $s->is_active ? 'Active' : 'Hidden' }}
              </span>
            </td>
            <td class="px-4 py-3 text-right align-middle">
              <a class="text-indigo-600 hover:underline" href="{{ route('admin.slides.edit',$s) }}">Edit</a>
              <form class="ml-3 inline" method="POST" action="{{ route('admin.slides.destroy',$s) }}" onsubmit="return confirm('Eliminare?')">
                @csrf @method('DELETE')
                <button class="text-rose-600 hover:underline">Delete</button>
              </form>
            </td>
          </tr>
        @empty
          <tr><td colspan="6" class="px-4 py-6 text-center text-gray-500 dark:text-gray-300">Add or create a slide. Empty for now!</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

  {{-- Pagination, se presente --}}
  @if(method_exists($slides,'links'))
    <div class="mt-4">{{ $slides->links() }}</div>
  @endif
</x-admin-layout>