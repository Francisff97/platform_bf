<section>
  <header class="mb-4">
    <h2 class="text-lg font-semibold">Profile information</h2>
    <p class="mt-1 text-sm opacity-70">Update your name, email and profile photo.</p>
  </header>

  {{-- resend verification --}}
  <form id="send-verification" method="post" action="{{ route('verification.send') }}">
    @csrf
  </form>

  <form method="post" action="{{ route('profile.update') }}" class="space-y-4" enctype="multipart/form-data">
    @csrf
    @method('patch')

    {{-- Name --}}
    <div>
      <x-input-label class="dark:text-white" for="name" :value="__('Name')" />
      <x-text-input id="name" name="name" type="text"
        class="mt-1 block w-full dark:bg-gray-900 dark:text-white"
        :value="old('name', $user->name)" required autofocus autocomplete="name" />
      <x-input-error class="mt-2" :messages="$errors->get('name')" />
    </div>

    {{-- Email --}}
    <div>
      <x-input-label class="dark:text-white" for="email" :value="__('Email')" />
      <x-text-input id="email" name="email" type="email"
        class="mt-1 block w-full dark:bg-gray-900 dark:text-white"
        :value="old('email', $user->email)" required autocomplete="username" />
      <x-input-error class="mt-2" :messages="$errors->get('email')" />

      @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
        <div class="mt-2 rounded-xl bg-amber-50 px-3 py-2 text-sm text-amber-900 ring-1 ring-amber-200 dark:bg-amber-900/30 dark:text-amber-100 dark:ring-amber-800">
          <span>Your email address is unverified.</span>
          <button form="send-verification"
                  class="ml-1 underline underline-offset-2 hover:opacity-90">
            Click here to re-send the verification email.
          </button>
          @if (session('status') === 'verification-link-sent')
            <div class="mt-1 text-emerald-600 dark:text-emerald-300">
              A new verification link has been sent to your email address.
            </div>
          @endif
        </div>
      @endif
    </div>

    {{-- Avatar --}}
    <div>
      <x-input-label class="dark:text-white" for="avatar" :value="__('Profile Photo')" />
      <input id="avatar" name="avatar" type="file" accept="image/*"
             class="mt-1 block w-full rounded-xl border px-3 py-2 ring-1 ring-black/10 dark:border-gray-800 dark:bg-gray-900 dark:text-white dark:ring-white/10">
      @if(auth()->user()->avatar_url)
        <img src="{{ auth()->user()->avatar_url }}"
             class="mt-2 h-12 w-12 rounded-full object-cover ring-1 ring-black/5 dark:ring-white/10" alt="">
      @endif
      <x-input-error class="mt-2" :messages="$errors->get('avatar')" />
    </div>

    <div class="flex items-center gap-3">
      <x-primary-button>Save</x-primary-button>

      @if (session('status') === 'profile-updated')
        <p x-data="{ show: true }" x-show="show" x-transition
           x-init="setTimeout(() => show = false, 2000)"
           class="text-sm opacity-70">Saved.</p>
      @endif
    </div>
  </form>
</section>