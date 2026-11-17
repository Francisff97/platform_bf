<x-app-layout title="Cookie Policy">
  <div class="rounded-2xl border bg-white/70 p-5 shadow-sm backdrop-blur dark:border-gray-800 dark:bg-gray-900/70">
    <h1 class="mb-2 text-2xl font-semibold">Cookie Policy</h1>
    @if($lastUpdated)
      <div class="mb-4 text-xs opacity-60">Last updated: {{ \Illuminate\Support\Carbon::parse($lastUpdated)->toFormattedDateString() }}</div>
    @endif
    <div class="prose max-w-none dark:prose-invert">
      {!! $html !!}
    </div>
  </div>
</x-app-layout>