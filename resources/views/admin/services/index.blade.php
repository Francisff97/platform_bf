<x-admin-layout title="Services">
  <div class="bg-gray-50 p-4 rounded border border-gray-200 dark:bg-gray-800 dark:border-gray-700 text-sm text-gray-600 dark:text-white mb-6">
    Welcome to the Services management page. Here you can create, edit, and delete services offered by your company. Use the "New Service" button to add a new service, and click "Edit" next to an existing service to modify its details or status.
  </div>
<div class="mb-4 flex items-center justify-between">
    <h2 class="text-lg font-semibold">Services</h2>
    <a href="{{ route('admin.services.create') }}"
       class="rounded bg-[var(--accent)] px-3 py-1.5 text-white text-sm hover:opacity-90">
      + Add Service
    </a>
  </div>

  <div class="overflow-hidden rounded-xl border bg-white shadow-sm dark:bg-gray-900 dark:text-white">
    <table class="min-w-full divide-y divide-gray-200">
      <thead class="bg-gray-50 text-left text-xs font-semibold uppercase text-gray-500">
        <tr>
            <th class="px-4 py-3">Image</th>
          <th class="px-4 py-3">Name</th>
          <th class="px-4 py-3">Status</th>
          <th class="px-4 py-3">Order</th>
          <th class="px-4 py-3 text-right">Actions</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-100">
        @foreach($services as $s)
          <tr class="hover:bg-gray-50">
              @if($s->image_path)
               <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">
                <img src="{{ Storage::url($s->image_path) }}" class="h-12 w-20 rounded object-cover">
               </td>
              @else — @endif
            <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">{{ $s->name }}</td>
            <td class="px-4 py-3">
              <span class="rounded-full px-2.5 py-0.5 text-xs font-medium {{ $s->status==='published' ? 'bg-green-50 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                {{ $s->status }}
              </span>
            </td>
            <td class="px-4 py-3 text-gray-700 dark:text-white">{{ $s->order }}</td>
            <td class="px-4 py-3 text-right">
              <a class="text-indigo-600 hover:underline mr-3" href="{{ route('admin.services.edit',$s) }}">Edit</a>
              <form class="inline" method="POST" action="{{ route('admin.services.destroy',$s) }}" onsubmit="return confirm('Eliminare?')">
                @csrf @method('DELETE')
                <button class="text-red-600 hover:underline">Delete</button>
              </form>
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>

  <div class="mt-4">{{ $services->links() }}</div>
</x-admin-layout>
