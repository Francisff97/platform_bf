<x-app-layout>
  <x-auto-hero />

  {{-- ===== Decor & helpers ===== --}}
  <style>
    .contact-shell{ position:relative; isolation:isolate }
    .contact-bg::before{
      content:""; position:absolute; inset:-4rem -10vw auto -10vw; height:360px; z-index:-1;
      background:
        radial-gradient(60rem 30rem at 10% -10%, color-mix(in oklab, var(--accent), white 40%) 0, transparent 60%),
        radial-gradient(50rem 25rem at 90% -20%, color-mix(in oklab, var(--accent), black 20%) 0, transparent 60%);
      opacity:.15;
      pointer-events:none;
    }
    .glass { background: linear-gradient(180deg, rgba(255,255,255,.75), rgba(255,255,255,.6)); backdrop-filter: blur(8px) }
    .dark .glass{ background: linear-gradient(180deg, rgba(17,24,39,.75), rgba(17,24,39,.55)) }
    .ring-gradient{
      position:relative; border-radius:22px; padding:1px;
      background:
        radial-gradient(900px 900px at var(--mx,60%) var(--my,0%),
          color-mix(in oklab, var(--accent), white 25%) 0, transparent 45%),
        linear-gradient(90deg,
          color-mix(in oklab, var(--accent), white 20%),
          color-mix(in oklab, var(--accent), black 20%));
    }
    .ring-gradient > .panel { border-radius:21px }
    /* Floating labels */
    .fl { position: relative }
    .fl input, .fl textarea {
      background: transparent;
      border-radius: 12px; border: 1px solid color-mix(in oklab, var(--accent), black 15%);
      padding: 1.1rem .9rem .5rem .9rem; width: 100%;
    }
    .dark .fl input, .dark .fl textarea { border-color: color-mix(in oklab, var(--accent), black 45%) }
    .fl label {
      pointer-events: none; position: absolute; left: .8rem; top: .9rem; font-size: .9rem;
      color: rgb(100 116 139); transition: all .15s ease;
    }
    .dark .fl label { color: rgb(148 163 184) }
    .fl input:focus + label, .fl input:not(:placeholder-shown) + label,
    .fl textarea:focus + label, .fl textarea:not(:placeholder-shown) + label {
      transform: translateY(-.65rem) scale(.85); background: color-mix(in oklab, var(--bg-light, #fff), transparent 0%);
      padding: 0 .25rem; color: var(--accent);
    }
    .dark .fl input:focus + label, .dark .fl input:not(:placeholder-shown) + label,
    .dark .fl textarea:focus + label, .dark .fl textarea:not(:placeholder-shown) + label {
      background: color-mix(in oklab, var(--bg-dark, #0b0f1a), transparent 0%)
    }
    /* Privacy checkbox */
    .fancy-check input{ appearance: none; width: 1.15rem; height: 1.15rem; border-radius: .4rem;
      border: 1.5px solid color-mix(in oklab, var(--accent), black 30%); display:inline-grid; place-content:center;
      background: white;
    }
    .dark .fancy-check input{ background:#0b0f1a; border-color: color-mix(in oklab, var(--accent), black 55%) }
    .fancy-check input::before{ content:""; width:.7rem; height:.7rem; transform: scale(0);
      transition: .12s transform ease-out; border-radius: .25rem; background: var(--accent);
    }
    .fancy-check input:checked::before{ transform: scale(1) }
    .btn-accent{
      background: var(--accent); color:#fff; transition:.15s ease;
      box-shadow: 0 8px 20px color-mix(in oklab, var(--accent), black 80% / 30%);
    }
    .btn-accent:hover{ filter: brightness(.98) transform: translateY(-1px) }
    .btn-accent:disabled{ opacity:.6; transform:none }
  </style>

  {{-- ===== Heading ===== --}}
  <section class="contact-shell contact-bg mx-auto mt-[60px] max-w-6xl px-4 text-center">
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

  {{-- ===== Form ===== --}}
  <section x-data="{loading:false}"
           class="mx-auto my-8 max-w-5xl px-4">
    <div class="ring-gradient" id="contactRing">
      <form method="POST" action="{{ route('contacts.submit') }}"
            class="panel glass">
        @csrf

        {{-- Mouse spotlight per la cornice --}}
        <script>
          document.addEventListener('mousemove', (e)=>{
            const box = document.getElementById('contactRing'); if(!box) return;
            const r = box.getBoundingClientRect();
            const x = Math.min(Math.max((e.clientX - r.left)/r.width, 0), 1);
            const y = Math.min(Math.max((e.clientY - r.top)/r.height, 0), 1);
            box.style.setProperty('--mx', (x*100)+'%');
            box.style.setProperty('--my', (y*100)+'%');
          });
        </script>

        <div class="grid gap-6 p-6 sm:p-8">
          <div class="grid gap-6 sm:grid-cols-2">
            {{-- Name --}}
            <div class="fl">
              <input name="name" value="{{ old('name') }}" placeholder=" " autocomplete="name"
                     class="dark:text-white @error('name') border-red-500 ring-red-500 @enderror" />
              <label>Discord name or full name</label>
              @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            {{-- Email --}}
            <div class="fl">
              <input name="email" type="email" value="{{ old('email') }}" placeholder=" " autocomplete="email"
                     class="dark:text-white @error('email') border-red-500 ring-red-500 @enderror" />
              <label>Email</label>
              @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
          </div>

          {{-- Subject --}}
          <div class="fl">
            <input name="subject" value="{{ old('subject') }}" placeholder=" "
                   class="dark:text-white" />
            <label>Subject (optional)</label>
          </div>

          {{-- Message --}}
          <div class="fl">
            <textarea name="message" rows="6" placeholder=" "
                      class="resize-y dark:text-white @error('message') border-red-500 ring-red-500 @enderror">{{ old('message') }}</textarea>
            <label>Your message</label>
            @error('message') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
          </div>

          {{-- Honeypot anti-spam (non compilare) --}}
          <div class="hidden">
            <label>Company</label>
            <input type="text" name="company" tabindex="-1" autocomplete="off" />
          </div>

          {{-- Privacy + reCAPTCHA --}}
          <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <label class="fancy-check inline-flex items-center gap-3 text-sm text-gray-700 dark:text-gray-300">
              <input type="checkbox" name="privacy" value="1" {{ old('privacy') ? 'checked' : '' }} required>
              <span>
                I accept the <a href="{{ route('privacy') ?? '#' }}" class="underline">Privacy Policy</a>
              </span>
            </label>

            {{-- token reCAPTCHA v3 verrà scritto qui --}}
            <input type="hidden" name="g-recaptcha-response" id="grecaptcha_token">
          </div>

          {{-- Submit --}}
          <div class="mt-2 flex items-center justify-between gap-3">
            <div class="text-xs text-gray-500 dark:text-gray-400">We usually reply within 24–48h.</div>
            <button type="submit"
                    @click="loading=true"
                    :disabled="loading"
                    class="btn-accent inline-flex items-center gap-2 rounded-xl px-5 py-3">
              <svg x-show="!loading" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
              <svg x-show="loading" class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="12" cy="12" r="10" opacity=".25"/><path d="M12 2a10 10 0 0 1 10 10" /></svg>
              <span x-text="loading ? 'Sending…' : 'Send request'">Send request</span>
            </button>
          </div>
        </div>
      </form>
    </div>
  </section>

  {{-- ===== reCAPTCHA v3 =====
       1) Sostituisci "YOUR_RECAPTCHA_SITE_KEY" con la tua SITE KEY v3
       2) Nel controller verifica il token con la SECRET KEY server-side
  --}}
  {{-- <script src="https://www.google.com/recaptcha/api.js?render=YOUR_RECAPTCHA_SITE_KEY"></script> --}}
  <script>
    // Abilita quando hai la SITE KEY:
    // const SITE_KEY = 'YOUR_RECAPTCHA_SITE_KEY';
    // document.addEventListener('DOMContentLoaded', () => {
    //   if (!window.grecaptcha || !SITE_KEY) return;
    //   grecaptcha.ready(function() {
    //     grecaptcha.execute(SITE_KEY, {action: 'contact'}).then(function(token) {
    //       const inp = document.getElementById('grecaptcha_token');
    //       if (inp) inp.value = token;
    //     });
    //   });
    // });
  </script>
</x-app-layout>
