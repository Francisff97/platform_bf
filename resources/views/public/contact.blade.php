{{-- resources/views/public/contact.blade.php --}}
<x-app-layout>
  <x-auto-hero />

  {{-- ===== Heading ===== --}}
  <section class="mx-auto mt-14 max-w-6xl px-4 text-center">
    <h1 class="font-orbitron text-4xl sm:text-5xl">Contact us</h1>
    <p class="mx-auto mt-3 max-w-2xl text-sm text-gray-600 dark:text-gray-400">
      Tell us about you and your project. We usually reply within 24–48h.
    </p>
  </section>

  {{-- ===== Flash messages ===== --}}
  @if (session('success'))
    <div class="mx-auto mt-6 max-w-3xl rounded-xl border border-emerald-300 bg-emerald-50/80 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-900/60 dark:bg-emerald-900/30 dark:text-emerald-100">
      {{ session('success') }}
    </div>
  @endif

  @if (session('error'))
    <div class="mx-auto mt-6 max-w-3xl rounded-xl border border-red-300 bg-red-50/80 px-4 py-3 text-sm text-red-700 dark:border-red-900/60 dark:bg-red-900/30 dark:text-red-100">
      {{ session('error') }}
    </div>
  @endif

  {{-- ===== Form card ===== --}}
  <section class="mx-auto my-10 max-w-5xl px-4">
    <form
      method="POST"
      action="{{ route('contacts.submit') }}"
      x-data="{loading:false}"
      @submit="loading=true"
      class="rounded-2xl border border-[color:var(--accent)]/25 bg-white/70 shadow-lg shadow-black/5 backdrop-blur-xl
             ring-1 ring-black/5 transition dark:border-[color:var(--accent)]/25 dark:bg-gray-900/60 dark:ring-white/10">
      @csrf

      <div class="grid gap-6 p-6 sm:p-8">
        {{-- Row 1 --}}
        <div class="grid gap-6 sm:grid-cols-2">
          {{-- Name --}}
          <div class="relative">
            <input
              name="name"
              value="{{ old('name') }}"
              placeholder=" "
              autocomplete="name"
              class="peer h-12 w-full rounded-xl border border-gray-300/60 bg-transparent px-4 text-[15px]
                     outline-none transition focus:border-[color:var(--accent)] focus:shadow focus:shadow-[color:var(--accent)]/10
                     dark:border-gray-700 dark:text-white" />
            <label
              class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 bg-transparent px-1 text-sm text-gray-500
                     transition-all
                     peer-focus:-top-2 peer-focus:text-[12px] peer-focus:text-[color:var(--accent)]
                     peer-[&:not(:placeholder-shown)]:-top-2 peer-[&:not(:placeholder-shown)]:text-[12px]
                     peer-[&:-webkit-autofill]:-top-2 peer-[&:-webkit-autofill]:text-[12px]
                     dark:text-gray-400">
              Discord name or full name
            </label>
            @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
          </div>

          {{-- Email --}}
          <div class="relative">
            <input
              name="email"
              type="email"
              value="{{ old('email') }}"
              placeholder=" "
              autocomplete="email"
              class="peer h-12 w-full rounded-xl border border-gray-300/60 bg-transparent px-4 text-[15px]
                     outline-none transition focus:border-[color:var(--accent)] focus:shadow focus:shadow-[color:var(--accent)]/10
                     dark:border-gray-700 dark:text-white" />
            <label
              class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 bg-transparent px-1 text-sm text-gray-500
                     transition-all
                     peer-focus:-top-2 peer-focus:text-[12px] peer-focus:text-[color:var(--accent)]
                     peer-[&:not(:placeholder-shown)]:-top-2 peer-[&:not(:placeholder-shown)]:text-[12px]
                     peer-[&:-webkit-autofill]:-top-2 peer-[&:-webkit-autofill]:text-[12px]
                     dark:text-gray-400">
              Email
            </label>
            @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
          </div>
        </div>

        {{-- Subject --}}
        <div class="relative">
          <input
            name="subject"
            value="{{ old('subject') }}"
            placeholder=" "
            class="peer h-12 w-full rounded-xl border border-gray-300/60 bg-transparent px-4 text-[15px]
                   outline-none transition focus:border-[color:var(--accent)] focus:shadow focus:shadow-[color:var(--accent)]/10
                   dark:border-gray-700 dark:text-white" />
          <label
            class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 bg-transparent px-1 text-sm text-gray-500
                   transition-all
                   peer-focus:-top-2 peer-focus:text-[12px] peer-focus:text-[color:var(--accent)]
                   peer-[&:not(:placeholder-shown)]:-top-2 peer-[&:not(:placeholder-shown)]:text-[12px]
                   peer-[&:-webkit-autofill]:-top-2 peer-[&:-webkit-autofill]:text-[12px]
                   dark:text-gray-400">
            Subject (optional)
          </label>
        </div>

        {{-- Message --}}
        <div class="relative">
          <textarea
            name="message"
            rows="6"
            placeholder=" "
            class="peer w-full rounded-xl border border-gray-300/60 bg-transparent p-4 text-[15px]
                   outline-none transition focus:border-[color:var(--accent)] focus:shadow focus:shadow-[color:var(--accent)]/10
                   dark:border-gray-700 dark:text-white">{{ old('message') }}</textarea>
          <label
            class="pointer-events-none absolute left-4 top-4 bg-transparent px-1 text-sm text-gray-500
                   transition-all
                   peer-focus:-top-2 peer-focus:text-[12px] peer-focus:text-[color:var(--accent)]
                   peer-[&:not(:placeholder-shown)]:-top-2 peer-[&:not(:placeholder-shown)]:text-[12px]
                   peer-[&:-webkit-autofill]:-top-2 peer-[&:-webkit-autofill]:text-[12px]
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
              I accept the <a href="#" class="underline" target="_blank">Privacy Policy</a>
            </span>
          </label>

          {{-- hidden token (riempito da reCAPTCHA v3) --}}
          <input type="hidden" name="g-recaptcha-response" id="grecaptcha_token">
        </div>

        {{-- Actions --}}
        <div class="mt-2 grid gap-3 sm:grid-cols-[1fr_auto] sm:items-center">
          <div class="text-xs text-gray-500 dark:text-gray-400">We usually reply within 24–48h.</div>
          <button type="submit"
                  :disabled="loading"
                  class="inline-flex items-center justify-center gap-2 rounded-xl bg-[color:var(--accent)] px-5 py-3 text-white shadow
                         transition hover:opacity-95 disabled:opacity-60">
            <svg x-show="!loading" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
            <svg x-show="loading" class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" stroke="currentColor">
              <circle cx="12" cy="12" r="10" opacity=".25"/>
              <path d="M12 2a10 10 0 0 1 10 10" />
            </svg>
            <span x-text="loading ? 'Sending…' : 'Send request'">Send request</span>
          </button>
        </div>
      </div>
            {{-- Legal note for reCAPTCHA --}}
      <p class="mt-6 text-center text-[11px] leading-snug text-gray-500 dark:text-gray-400 opacity-80">
        This site is protected by reCAPTCHA and the Google
        <a href="https://policies.google.com/privacy" target="_blank" class="underline hover:text-[color:var(--accent)]">Privacy Policy</a>
        and
        <a href="https://policies.google.com/terms" target="_blank" class="underline hover:text-[color:var(--accent)]">Terms of Service</a>
        apply.
      </p>
    </form>
  </section>

  @php
    $recaptchaSiteKey = config('services.recaptcha.site_key')
      ?? optional(\App\Models\SiteSetting::first())->recaptcha_site_key
      ?? null;
  @endphp

  @if($recaptchaSiteKey)
    {{-- reCAPTCHA v3 --}}
    <script src="https://www.google.com/recaptcha/api.js?render={{ $recaptchaSiteKey }}"></script>
    <script>
      document.addEventListener('DOMContentLoaded', () => {
        const siteKey = @json($recaptchaSiteKey);
        if (!window.grecaptcha) return;
        grecaptcha.ready(() => {
          grecaptcha.execute(siteKey, {action: 'contact'}).then((token) => {
            const inp = document.getElementById('grecaptcha_token');
            if (inp) inp.value = token;
          });
        });
      });
    </script>
  @endif
</x-app-layout>

{{-- ===== UI helpers & autofill fixes ===== --}}
<style>
  /* Elimina il giallo dell’autofill e mantiene colori corretti */
  input:-webkit-autofill,
  textarea:-webkit-autofill {
    -webkit-box-shadow: 0 0 0px 1000px transparent inset !important;
    transition: background-color 9999s ease-in-out 0s !important;
  }
  /* Testo nero in light */
  input:-webkit-autofill,
  textarea:-webkit-autofill {
    -webkit-text-fill-color: #0f172a !important; /* slate-900 */
    caret-color: #0f172a !important;
  }
  /* Testo bianco in dark */
  .dark input:-webkit-autofill,
  .dark textarea:-webkit-autofill {
    -webkit-text-fill-color: #fff !important;
    caret-color: #fff !important;
  }

  /* NASCONDE il badge reCAPTCHA v3.
     Nota: Google consiglia di lasciarlo visibile e includere una nota/Privacy.
     Se lo nascondi, assicurati di menzionare reCAPTCHA e le policy altrove. */
  .grecaptcha-badge{
    opacity: 0;
    pointer-events: none;
  }
</style>