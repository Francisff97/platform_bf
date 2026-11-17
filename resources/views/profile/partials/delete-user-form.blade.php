<section class="space-y-4">
  <header>
    <h2 class="text-lg font-semibold">Delete account</h2>
    <p class="mt-1 text-sm opacity-70">
      Once deleted, all your data will be permanently removed. Download anything you want to keep.
    </p>
  </header>

  <x-danger-button
    x-data
    x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')">
    Delete account
  </x-danger-button>

  <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
    <form method="post" action="{{ route('profile.destroy') }}"
          class="space-y-4 p-6">
      @csrf
      @method('delete')

      <h2 class="text-lg font-semibold">Are you sure?</h2>
      <p class="text-sm opacity-80">
        Please enter your password to confirm you want to permanently delete your account.
      </p>

      <div>
        <x-input-label for="password" :value="__('Password')" class="sr-only" />
        <x-text-input id="password" name="password" type="password"
          class="mt-1 block w-full dark:text-white" placeholder="{{ __('Password') }}" />
        <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2" />
      </div>

      <div class="mt-2 flex items-center justify-end gap-2">
        <x-secondary-button x-on:click="$dispatch('close')">Cancel</x-secondary-button>
        <x-danger-button>Delete account</x-danger-button>
      </div>
    </form>
  </x-modal>
</section>