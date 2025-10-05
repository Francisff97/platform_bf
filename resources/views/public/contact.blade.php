<x-app-layout>
<x-auto-hero />

  <h1 class="text-[3rem] my-[50px]">Contact us</h1>
  <form method="POST" action="{{ route('contacts.submit') }}" class="grid gap-3 max-w-6xl">
    @csrf
    <input name="name" placeholder="Discord name or full name" class="border p-2 rounded" value="{{ old('name') }}">
    <input name="email" placeholder="Email" class="border p-2 rounded" value="{{ old('email') }}">
    <input name="subject" placeholder="Object (optional)" class="border p-2 rounded" value="{{ old('subject') }}">
    <textarea name="message" placeholder="Message" class="border p-2 rounded" rows="5">{{ old('message') }}</textarea>
    <button class="rounded bg-[var(--accent)] px-4 py-2 text-white hover:bg-indigo-500">Send request</button>
    @error('name') <div class="text-red-600 text-sm">{{ $message }}</div> @enderror
    @error('email') <div class="text-red-600 text-sm">{{ $message }}</div> @enderror
    @error('message') <div class="text-red-600 text-sm">{{ $message }}</div> @enderror
  </form>
</x-app-layout>
