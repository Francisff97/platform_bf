<x-admin-layout title="Tutorials">
  @if (session('success'))
    <div class="mb-4 rounded border border-green-300 bg-green-50 px-3 py-2 text-sm text-green-800">
      {{ session('success') }}
    </div>
  @endif

  <div class="mb-4 flex items-center justify-between">
    <h2 class="text-lg font-semibold">Tutorials</h2>
    <a href="{{ route('admin.addons.tutorials.create') }}"
       class="rounded bg-[var(--accent)] px-3 py-1.5 text-white text-sm">+ New</a>
  </div>

  <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
    <div class="rounded-xl border p-4 dark:border-gray-800">
      <div class="mb-2 text-sm font-semibold">Packs</div>
      <ul class="space-y-1 text-sm">
        @foreach($packs as $p)
          <li class="flex justify-between">
            <span class="truncate">{{ $p->title }}</span>
            <span class="text-gray-500">{{ $p->tutorials_count }}</span>
          </li>
        @endforeach
      </ul>
    </div>

    <div class="rounded-xl border p-4 dark:border-gray-800">
      <div class="mb-2 text-sm font-semibold">Coaches</div>
      <ul class="space-y-1 text-sm">
        @foreach($coaches as $c)
          <li class="flex justify-between">
            <span class="truncate">{{ $c->name }}</span>
            <span class="text-gray-500">{{ $c->tutorials_count }}</span>
          </li>
        @endforeach
      </ul>
    </div>

    <div class="rounded-xl border p-4 dark:border-gray-800 lg:col-span-1">
      <div class="mb-2 text-sm font-semibold">How it works</div>
      <p class="text-sm text-gray-600 dark:text-gray-400">
        Aggiungi link YouTube/Vimeo ai Pack o ai Coach. Imposta
        <em>Public</em> o <em>Solo acquirenti</em>.
      </p>
    </div>
  </div>

  <div class="mt-6 rounded-xl border p-4 dark:border-gray-800">
    <div class="mb-3 text-sm font-semibold">Ultimi tutorial</div>
    <div class="overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead>
          <tr class="text-left text-gray-500 border-b dark:border-gray-800">
            <th class="py-2 pr-4">Title</th>
            <th class="py-2 pr-4">Entity</th>
            <th class="py-2 pr-4">Visibility</th>
            <th class="py-2 pr-4">URL</th>
            <th class="py-2"></th>
          </tr>
        </thead>
        <tbody>
          @foreach($tutorials as $t)
            <tr class="border-b last:border-0 dark:border-gray-800">
              <td class="py-2 pr-4">{{ $t->title }}</td>
              <td class="py-2 pr-4">
                @php $e = $t->tutorialable; @endphp
                @if($e instanceof \App\Models\Pack) Pack: {{ $e->title }}
                @elseif($e instanceof \App\Models\Coach) Coach: {{ $e->name }}
                @else - @endif
              </td>
              <td class="py-2 pr-4">
                {{ $t->is_public ? 'Public' : 'Only buyers' }}
              </td>
              <td class="py-2 pr-4 truncate max-w-[260px]">
                <a href="{{ $t->video_url }}" target="_blank" class="text-indigo-600 hover:underline">Open</a>
              </td>
              <td class="py-2 text-right">
                <a href="{{ route('admin.addons.tutorials.edit',$t) }}" class="text-indigo-600 hover:underline">Edit</a>
                <form action="{{ route('admin.addons.tutorials.destroy',$t) }}" method="POST" class="inline"
                      onsubmit="return confirm('Delete tutorial?')">
                  @csrf @method('DELETE')
                  <button class="ml-2 text-red-600 hover:underline">Delete</button>
                </form>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>

    <div class="mt-3">
      {{ $tutorials->links() }}
    </div>
  </div>
</x-admin-layout>