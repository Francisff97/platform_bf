<section>
  <header class="mb-4">
    <h2 class="text-lg font-semibold">Update password</h2>
    <p class="mt-1 text-sm opacity-70">
      Use a strong, unique password to keep your account secure.
    </p>
  </header>

  <form method="post" action="{{ route('password.update') }}" class="space-y-4">
    @csrf
    @method('put')

    <div>
      <x-input-label class="dark:text-white" for="update_password_current_password" :value="__('Current Password')" />
      <x-text-input id="update_password_current_password" name="current_password" type="password"
        class="mt-1 block w-full dark:bg-gray-900 dark:text-white" autocomplete="current-password" />
      <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2" />
    </div>

    <div>
      <x-input-label class="dark:text-white" for="update_password_password" :value="__('New Password')" />
      <x-text-input id="update_password_password" name="password" type="password"
        class="mt-1 block w-full dark:bg-gray-900 dark:text-white" autocomplete="new-password" />
      <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2" />
    </div>

    <div>
      <x-input-label for="update_password_password_confirmation" :value="__('Confirm Password')" />
      <x-text-input id="update_password_password_confirmation" name="password_confirmation" type="password"
        class="mt-1 block w-full dark:bg-gray-900 dark:text-white" autocomplete="new-password" />
      <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2" />
    </div>

    <div class="flex items-center gap-3">
      <x-primary-button>Save</x-primary-button>

      @if (session('status') === 'password-updated')
        <p x-data="{ show: true }" x-show="show" x-transition
           x-init="setTimeout(() => show = false, 2000)"
           class="text-sm opacity-70">Saved.</p>
      @endif
    </div>
  </form>
</section>