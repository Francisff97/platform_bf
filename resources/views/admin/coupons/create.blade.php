<x-admin-layout title="New coupon">
  @if ($errors->any())
    <div class="mb-4 rounded border border-red-300 bg-red-50 px-3 py-2 text-sm text-red-700">
      <div class="font-semibold mb-1">Please fix errors:</div>
      <ul class="list-disc pl-5">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
  @endif

  <form method="POST" action="{{ route('admin.coupons.store') }}"
        class="grid max-w-3xl gap-6 rounded-2xl border bg-white/70 p-4 shadow-sm backdrop-blur dark:border-gray-800 dark:bg-gray-900/70">
    @csrf
    @include('admin.coupons._form', ['coupon' => $coupon])
    <div class="flex items-center gap-3">
      <button class="rounded bg-[var(--accent)] px-4 py-2 text-white">Save</button>
      <a href="{{ route('admin.coupons.index') }}" class="underline">Cancel</a>
    </div>
  </form>
</x-admin-layout>