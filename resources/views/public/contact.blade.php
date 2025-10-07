<x-app-layout>
  <x-auto-hero />

  <h1 class="text-[3rem] my-[50px]">Contact us</h1>

  @if (session('success'))
    <div class="mb-4 rounded border border-green-300 bg-green-50 px-3 py-2 text-sm text-green-800">
      {{ session('success') }}
    </div>
  @endif

  <form method="POST" action="{{ route('contacts.submit') }}" class="grid gap-3 max-w-6xl">
    @csrf

    <input
      name="name"
      placeholder="Discord name or full name"
      class="border p-2 rounded bg-white text-black placeholder:text-gray-500 dark:bg-white dark:text-black"
      value="{{ old('name') }}"
    >

    <input
      name="email"
      type="email"
      placeholder="Email"
      class="border p-2 rounded bg-white text-black placeholder:text-gray-500 dark:bg-white dark:text-black"
      value="{{ old('email') }}"
    >

    <input
      name="subject"
      placeholder="Object (optional)"
      class="border p-2 rounded bg-white text-black placeholder:text-gray-500 dark:bg-white dark:text-black"
      value="{{ old('subject') }}"
    >

    <textarea
      name="message"
      placeholder="Message"
      rows="5"
      class="border p-2 rounded bg-white text-black placeholder:text-gray-500 dark:bg-white dark:text-black"
    >{{ old('message') }}</textarea>

    <button class="rounded bg-[var(--accent)] px-4 py-2 text-white hover:bg-indigo-500">
      Send request
    </button>

    @error('name')    <div class="text-red-600 text-sm">{{ $message }}</div> @enderror
    @error('email')   <div class="text-red-600 text-sm">{{ $message }}</div> @enderror
    @error('message') <div class="text-red-600 text-sm">{{ $message }}</div> @enderror
  </form>
</x-app-layout>
