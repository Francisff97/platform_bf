@props([])

<div class="mt-1 rounded-lg border border-[color:var(--accent)]/40 bg-[color:var(--accent)]/5 px-3 py-2 text-xs text-gray-700 dark:text-gray-200 dark:border-[color:var(--accent)]/30 dark:bg-[color:var(--accent)]/10">
  <div class="flex items-start gap-2">
    <svg class="mt-0.5 h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor">
      <circle cx="12" cy="12" r="10" stroke-width="1.5"/>
      <path d="M8 13l3-3 5 6" stroke-width="1.5"/>
    </svg>
    <div>
      <div class="font-medium">Image guidelines</div>
      <div class="mt-0.5 flex flex-wrap gap-x-3 gap-y-0.5">
        <span><strong>Recommended size:</strong> {{ $hint['size'] }}</span>
        <span><strong>Aspect ratio:</strong> {{ $hint['ratio'] }}</span>
        <span><strong>Max weight:</strong> {{ $hint['max'] }}</span>
        <span><strong>Notes:</strong> {{ $hint['notes'] }}</span>
      </div>
    </div>
  </div>
</div>