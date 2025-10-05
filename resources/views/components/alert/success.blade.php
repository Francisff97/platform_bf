@props([
  'message' => null,
  'dismissible' => true,
])

<div {{ $attributes->class([
      'relative rounded border border-green-300 bg-green-50 px-3 py-2 text-sm text-green-800',
    ]) }}>
  @if($message)
    {{ $message }}
  @else
    {{ $slot }}
  @endif

  @if($dismissible)
    <button type="button"
            onclick="this.closest('div').remove()"
            class="absolute right-2 top-1.5 inline-flex items-center text-green-700/70 hover:text-green-900"
            aria-label="Close">
      ✕
    </button>
  @endif
</div>