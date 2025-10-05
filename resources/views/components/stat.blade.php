@props(['label','value', 'icon' => null, 'muted' => null])

<div class="rounded-xl border bg-white p-5 shadow-sm">
  <div class="flex items-center gap-3">
    @if($icon)
      <div class="rounded-lg bg-indigo-50 p-2 text-indigo-600">
        {!! $icon !!}
      </div>
    @endif
    <div>
      <div class="text-sm text-gray-500">{{ $label }}</div>
      <div class="text-2xl font-semibold mt-1">{{ $value }}</div>
      @if($muted)
        <div class="text-xs text-gray-400 mt-1">{{ $muted }}</div>
      @endif
    </div>
  </div>
</div>
