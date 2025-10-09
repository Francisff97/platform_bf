<x-app-layout>
  <x-auto-hero />

  {{-- ===== Heading ===== --}}
  <section class="mx-auto mt-14 max-w-6xl px-4 text-center">
    <h1 class="font-orbitron text-4xl sm:text-5xl">Contact us</h1>
    <p class="mx-auto mt-3 max-w-2xl text-sm text-gray-600 dark:text-gray-400">
      Tell us a bit about you and your project. We usually reply within 24–48h.
    </p>
  </section>

  {{-- ===== Flash success ===== --}}
  @if (session('success'))
    <div class="mx-auto mt-6 max-w-3xl rounded-xl border border-green-300 bg-green-50/70 px-4 py-3 text-sm text-green-800">
      {{ session('success') }}
    </div>
  @endif

  {{-- ===== Form card (no wallpaper) ===== --}}
  <section x-data="{loading:false}" class="mx-auto my-10 max-w-5xl px-4">
    <form method="POST" action="{{ route('contacts.submit') }}"
          class="rounded-2xl border border-[color:var(--accent)]/25 bg-white/70 shadow-sm backdrop-blur-md
                 dark:border-[color:var(--accent)]/25 dark:bg-gray-900/60">
      @csrf

      <div class="grid gap-6 p-6 sm:p-8">
        <div class="grid gap-6 sm:grid-cols-2">
          {{-- Name --}}
          <div class="relative">
            <input name="name" value="{{ old('name') }}" placeholder=" "
                   autocomplete="name"
                   class="peer h-12 w-full rounded-xl border border-gray-300/60 bg-transparent px-4 text-[15px]
                          outline-none transition focus:border-[color:var(--accent)] focus:ring-2 focus:ring-[color:var(--accent)]/20
                          dark:border-gray-700 dark:text-white @error('name') border-red-500 focus:ring-red-500 @enderror"/>
            <label class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 bg-transparent px-1 text-sm text-gray-500
                           transition-all peer-focus:-top-2 peer-focus:text-[12px] peer-focus:text-[color:var(--accent)]
                           peer-placeholder-shown:top-1/2 peer-placeholder-shown:-translate-y-1/2
                           peer-not-placeholder-shown:-top-2 peer-not-placeholder-shown:text-[12px]
                           dark:text-gray-400">
              Discord name or full name
            </label>
            @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
          </div>

          {{-- Email --}}
          <div class="relative">
            <input name="email" type="email" value="{{ old('email') }}" placeholder=" "
                   autocomplete="email"
                   class="peer h-12 w-full rounded-xl border border-gray-300/60 bg-transparent px-4 text-[15px]
                          outline-none transition focus:border-[color:var(--accent)] focus:ring-2 focus:ring-[color:var(--accent)]/20
                          dark:border-gray-700 dark:text-white @error('email') border-red-500 focus:ring-red-500 @enderror"/>
            <label class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 bg-transparent px-1 text-sm text-gray-500
                           transition-all peer-focus:-top-2 peer-focus:text-[12px] peer-focus:text-[color:var(--accent)]
                           peer-placeholder-shown:top-1/2 peer-placeholder-shown:-translate-y-1/2
                           peer-not-placeholder-shown:-top-2 peer-not-placeholder-shown:text-[12px]
                           dark:text-gray-400">
              Email
            </label>
            @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
          </div>
        </div>

        {{-- Subject --}}
        <div class="relative">
          <input name="subject" value="{{ old('subject') }}" placeholder=" "
                 class="peer h-12 w-full rounded-xl border border-gray-300/60 bg-transparent px-4 text-[15px]
                        outline-none transition focus:border-[color:var(--accent)] focus:ring-2 focus:ring-[color:var(--accent)]/20
                        dark:border-gray-700 dark:text-white"/>
          <label class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 bg-transparent px-1 text-sm text-gray-500
                         transition-all peer-focus:-top-2 peer-focus:text-[12px] peer-focus:text-[color:var(--accent)]
                         peer-placeholder-shown:top-1/2 peer-placeholder-shown:-translate-y-1/2
                         peer-not-placeholder-shown:-top-2 peer-not-placeholder-shown:text-[12px]
                         dark:text-gray-400">
            Subject (optional)
          </label>
        </div>

        {{-- Message --}}
        <div class="relative">
          <textarea name="message" rows="6" placeholder=" "
                    class="peer w-full rounded-xl border border-gray-300/60 bg-transparent p-4 text-[15px]
                           outline-none transition focus:border-[color:var(--accent)] focus:ring-2 focus:ring-[color:var(--accent)]/20
                           dark:border-gray-700 dark:text-white @error('message') border-red-500 focus:ring-red-500 @enderror">{{ old('message') }}</textarea>
          <label class="pointer-events-none absolute left-4 top-4 bg-transparent px-1 text-sm text-gray-500
                         transition-all peer-focus:-top-2 peer-focus:text-[12px] peer-focus:text-[color:var(--accent)]
                         peer-not-placeholder-shown:-top-2 peer-not-placeholder-shown:text-[12px]
                         dark:text-gray-400">
            Your message
          </label>
          @error('message') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        {{-- Honeypot anti-spam --}}
        <div class="hidden">
          <label>Company</label>
          <input type="text" name="company" tabindex="-1" autocomplete="off" />
        </div>

        {{-- Privacy + reCAPTCHA --}}
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
          <label class="inline-flex items-center gap-3 text-sm text-gray-700 dark:text-gray-300">
            <input type="checkbox" name="privacy" value="1" required
                   class="h-4 w-4 appearance-none rounded border border-gray-400/70 bg-white transition
                          checked:bg-[color:var(--accent)] checked:border-[color:var(--accent)]
                          dark:border-gray-600 dark:bg-gray-900"/>
            <span>
              I accept the <a href="#" class="underline">Privacy Policy</a>
            </span>
          </label>

          {{-- hidden token (riempito da reCAPTCHA v3) --}}
          <input type="hidden" name="g-recaptcha-response" id="grecaptcha_token">
        </div>

        {{-- Actions --}}
        <div class="mt-2 flex items-center justify-between gap-3">
          <div class="text-xs text-gray-500 dark:text-gray-400">We usually reply within 24–48h.</div>
          <button type="submit"
                  @click="loading=true" :disabled="loading"
                  class="inline-flex items-center gap-2 rounded-xl bg-[color:var(--accent)] px-5 py-3 text-white shadow
                         transition hover:opacity-90 disabled:opacity-60">
            <svg x-show="!loading" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
            <svg x-show="loading" class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" stroke="currentColor">
              <circle cx="12" cy="12" r="10" opacity=".25"/>
              <path d="M12 2a10 10 0 0 1 10 10" />
            </svg>
            <span x-text="loading ? 'Sending…' : 'Send request'">Send request</span>
          </button>
        </div>
      </div>
    </form>
  </section>

  {{-- ===== reCAPTCHA v3 (pronto ma COMMENTATO) =====
       1) Sostituisci "YOUR_RECAPTCHA_SITE_KEY" con la tua SITE KEY v3
       2) Nel tuo controller verifica server-side con la SECRET KEY
  --}}
  {{-- <script src="https://www.google.com/recaptcha/api.js?render=YOUR_RECAPTCHA_SITE_KEY"></script>
  <script>
    const SITE_KEY = 'YOUR_RECAPTCHA_SITE_KEY';
    document.addEventListener('DOMContentLoaded', () => {
      if (!window.grecaptcha || !SITE_KEY) return;
      grecaptcha.ready(function() {
        grecaptcha.execute(SITE_KEY, {action: 'contact'}).then(function(token) {
          const inp = document.getElementById('grecaptcha_token');
          if (inp) inp.value = token;
        });
      });
    });
  </script> --}}
</x-app-layout>
