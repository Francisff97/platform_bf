<x-app-layout>
  <x-auto-hero />

  <h1 class="text-[3rem] my-[50px]">Contact us</h1>

  @if (session('success'))
    <div class="mb-6 rounded-xl border border-green-300 bg-green-50/70 px-4 py-3 text-sm text-green-800">
      {{ session('success') }}
    </div>
  @endif

  <form method="POST" action="{{ route('contacts.submit') }}"
        class="mx-auto grid w-full max-w-5xl gap-4 rounded-2xl border border-[color:var(--accent)]/30 bg-white/60 p-6 backdrop-blur
               dark:border-[color:var(--accent)]/30 dark:bg-black/40">
    @csrf

    <div class="grid gap-4 md:grid-cols-2">
      {{-- Name --}}
      <input
        name="name"
        placeholder="Discord name or full name"
        value="{{ old('name') }}"
        class="h-12 w-full rounded-xl border border-[color:var(--accent)] bg-white/90 px-4 text-black placeholder:text-gray-500 outline-none transition
               focus:ring-2 focus:ring-[color:var(--accent)] focus:ring-offset-0
               dark:bg-black/80 dark:text-white dark:placeholder:text-gray-400
               @error('name') border-red-500 focus:ring-red-500 @enderror"
        autocomplete="name"
      />

      {{-- Email --}}
      <input
        name="email"
        type="email"
        placeholder="Email"
        value="{{ old('email') }}"
        class="h-12 w-full rounded-xl border border-[color:var(--accent)] bg-white/90 px-4 text-black placeholder:text-gray-500 outline-none transition
               focus:ring-2 focus:ring-[color:var(--accent)] focus:ring-offset-0
               dark:bg-black/80 dark:text-white dark:placeholder:text-gray-400
               @error('email') border-red-500 focus:ring-red-500 @enderror"
        autocomplete="email"
      />
    </div>

    {{-- Subject --}}
    <input
      name="subject"
      placeholder="Object (optional)"
      value="{{ old('subject') }}"
      class="h-12 w-full rounded-xl border border-[color:var(--accent)] bg-white/90 px-4 text-black placeholder:text-gray-500 outline-none transition
             focus:ring-2 focus:ring-[color:var(--accent)] focus:ring-offset-0
             dark:bg-black/80 dark:text-white dark:placeholder:text-gray-400"
    />

    {{-- Message --}}
    <textarea
      name="message"
      rows="5"
      placeholder="Message"
      class="w-full rounded-xl border border-[color:var(--accent)] bg-white/90 p-4 text-black placeholder:text-gray-500 outline-none transition
             focus:ring-2 focus:ring-[color:var(--accent)] focus:ring-offset-0
             dark:bg-black/80 dark:text-white dark:placeholder:text-gray-400
             @error('message') border-red-500 focus:ring-red-500 @enderror"
    >{{ old('message') }}</textarea>

    {{-- Errors inline --}}
    <div class="grid gap-1 text-sm">
      @error('name')    <div class="text-red-600">{{ $message }}</div> @enderror
      @error('email')   <div class="text-red-600">{{ $message }}</div> @enderror
      @error('message') <div class="text-red-600">{{ $message }}</div> @enderror
    </div>

    {{-- Actions --}}
    <div class="mt-2 flex items-center justify-between gap-3">
      <p class="text-xs text-gray-500 dark:text-gray-400">
        We usually reply within 24–48h.
      </p>

      <button
        class="inline-flex items-center justify-center rounded-xl bg-[color:var(--accent)] px-5 py-3 text-white transition
               hover:opacity-90 active:opacity-80 focus:outline-none focus-visible:ring-2 focus-visible:ring-white/60"
        type="submit"
      >
        Send request
      </button>
    </div>
  </form>
</x-app-layout>