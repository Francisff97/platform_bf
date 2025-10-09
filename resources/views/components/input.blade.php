@props([
  'label' => null,
  'name',
  'type' => 'text',
  'value' => null,
  'required' => false,
  'class' => '',
])

<div>
  @if($label)
    <label for="{{ $name }}" class="mb-1 block text-sm font-medium text-gray-800 dark:text-gray-200">
      {{ $label }} @if($required)<span class="text-red-500">*</span>@endif
    </label>
  @endif

  <input
    id="{{ $name }}"
    name="{{ $name }}"
    type="{{ $type }}"
    value="{{ old($name, $value) }}"
    {{ $required ? 'required' : '' }}
    {{ $attributes->merge([
      'class' => "h-11 w-full rounded-xl border border-[color:var(--accent)] bg-white/90 px-3 text-black
                  placeholder:text-gray-500 outline-none transition
                  hover:border-[color:var(--accent)]/80 focus:ring-2 focus:ring-[color:var(--accent)]
                  dark:bg-black/80 dark:text-white dark:placeholder:text-gray-400 $class"
    ]) }}
  />

  @error($name)
    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
  @enderror
</div>
