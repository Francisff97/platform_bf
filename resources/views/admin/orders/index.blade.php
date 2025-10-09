<x-admin-layout title="Orders">
  <h1 class="text-xl font-bold mb-4">Orders</h1>
  <table class="min-w-full border dark:border-gray-600">
    <thead class="bg-gray-100 dark:bg-gray-900 dark:text-white">
      <tr>
        <th class="px-3 py-2">#</th>
        <th class="px-3 py-2">Customer</th>
        <th class="px-3 py-2">Total</th>
        <th class="px-3 py-2">Status</th>
        <th class="px-3 py-2">Created</th>
        <th class="px-3 py-2"></th>
      </tr>
    </thead>
    <tbody>
      @foreach($orders as $o)
        <tr class="border-t dark:border-gray-600">
          <td class="px-3 py-2">{{ $o->id }}</td>
          <td class="px-3 py-2">{{ $o->meta['customer']['full_name'] ?? '-' }}</td>
          <td class="px-3 py-2">{{ number_format($o->amount_cents/100,2,',','.') }} {{ $o->currency }}</td>
          <td class="px-3 py-2">{{ ucfirst($o->status) }}</td>
          <td class="px-3 py-2">{{ $o->created_at->format('d/m/Y H:i') }}</td>
          <td class="px-3 py-2 flex gap-2">
            <a href="{{ route('admin.orders.show',$o) }}" class="text-indigo-600 underline">Have a look</a>

            <form action="{{ route('admin.orders.destroy',$o) }}" method="POST" onsubmit="return confirm('Sei sicuro di voler eliminare questo ordine?');">
              @csrf
              @method('DELETE')
              <button type="submit" class="text-red-600 underline">Delete</button>
            </form>
          </td>
        </tr>
      @endforeach
    </tbody>
  </table>
  <div class="mt-4">{{ $orders->links() }}</div>
</x-admin-layout>
