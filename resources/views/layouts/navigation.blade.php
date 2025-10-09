<nav x-data="{ open:false }" class="border-b bg-[var(--bg-light)] dark:bg-[var(--bg-dark)] border-gray-200 dark:border-gray-800">
  <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
    <div class="flex h-16 justify-between">
      <div class="flex">
        {{-- Logo --}}
        @php $s = \App\Models\SiteSetting::first(); @endphp
        <a href="{{ route('home') }}" class="flex items-center gap-2">
          @if($s?->logo_light_path || $s?->logo_dark_path)
            <img src="{{ $s?->logo_light_path ? Storage::url($s->logo_light_path) : '' }}"
                 class="h-7 w-auto dark:hidden" alt="Logo">
            <img src="{{ $s?->logo_dark_path ? Storage::url($s->logo_dark_path) : '' }}"
                 class="hidden h-7 w-auto dark:block" alt="Logo Dark">
          @else
            <span class="font-bold">Blueprint</span>
          @endif
        </a>

        {{-- Primary nav --}}
        <div class="ml-10 hidden items-center space-x-8 sm:flex">
          <x-nav-link :href="route('home')"            :active="request()->routeIs('home')">Home</x-nav-link>
          <x-nav-link :href="route('about')"           :active="request()->routeIs('about')">About</x-nav-link>
          <x-nav-link :href="route('builders.index')"  :active="request()->routeIs('builders.*')">Builders</x-nav-link>
          <x-nav-link :href="route('services.public')" :active="request()->routeIs('services.public')">Services</x-nav-link>
          <x-nav-link :href="route('packs.public')"    :active="request()->routeIs('packs.*')">Packs</x-nav-link>
          <x-nav-link :href="route('contacts')"        :active="request()->routeIs('contacts')">Contacts</x-nav-link>
          @auth
            <x-nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.*')">Admin</x-nav-link>
          @endauth
        </div>
      </div>

      {{-- Right area --}}
      <div class="hidden items-center space-x-3 sm:flex">
        {{-- Theme pill --}}
        <div class="inline-flex rounded-full border px-1 py-1 text-xs dark:border-gray-700">
          <button class="rounded-full px-2 py-1" onclick="setTheme('light')">Light</button>
          <button class="rounded-full px-2 py-1" onclick="setTheme('dark')">Dark</button>
          <button class="rounded-full px-2 py-1" onclick="setTheme('system')">Auto</button>
        </div>

        @auth
          <x-dropdown align="right" width="48">
            <x-slot name="trigger">
              <button class="inline-flex items-center rounded-md px-3 py-2 text-sm hover:bg-gray-50 dark:hover:bg-gray-800">
                {{ Auth::user()->name }}
                <svg class="ml-2 h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.24a.75.75 0 01-1.06 0L5.21 8.29a.75.75 0 01.02-1.08z" clip-rule="evenodd"/></svg>
              </button>
            </x-slot>
            <x-slot name="content">
              <x-dropdown-link :href="route('profile.edit')">Profile</x-dropdown-link>
              <form method="POST" action="{{ route('logout') }}">@csrf
                <x-dropdown-link :href="route('logout')"
                  onclick="event.preventDefault(); this.closest('form').submit();">
                  Log out
                </x-dropdown-link>
              </form>
            </x-slot>
          </x-dropdown>
        @else
          <a href="{{ route('login') }}" class="rounded-md px-3 py-2 text-sm font-medium hover:bg-gray-50 dark:hover:bg-gray-800">Login</a>
        @endauth
      </div>

      {{-- Mobile hamburger --}}
      <div class="-mr-2 flex items-center sm:hidden">
        <button @click="open=!open" class="rounded-md p-2 hover:bg-gray-100 dark:hover:bg-gray-800">
          <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
            <path :class="{'hidden': open, 'inline-flex': !open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M4 6h16M4 12h16M4 18h16"/>
            <path :class="{'hidden': !open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M6 18L18 6M6 6l12 12"/>
          </svg>
        </button>
      </div>
    </div>
  </div>

  {{-- Mobile menu --}}
  <div :class="{'block': open, 'hidden': !open}" class="hidden border-t sm:hidden dark:border-gray-800">
    <div class="space-y-1 pb-3 pt-2">
      <x-responsive-nav-link :href="route('home')"            :active="request()->routeIs('home')">Home</x-responsive-nav-link>
      <x-responsive-nav-link :href="route('about')"           :active="request()->routeIs('about')">About</x-responsive-nav-link>
      <x-responsive-nav-link :href="route('services.public')" :active="request()->routeIs('services.public')">Services</x-responsive-nav-link>
      <x-responsive-nav-link :href="route('builders.index')"  :active="request()->routeIs('builders.*')">Builders</x-responsive-nav-link>
      <x-responsive-nav-link :href="route('packs.public')"    :active="request()->routeIs('packs.*')">Packs</x-responsive-nav-link>
      <x-responsive-nav-link :href="route('contacts')"        :active="request()->routeIs('contacts')">Contacts</x-responsive-nav-link>
      @auth
        <x-responsive-nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.*')">Admin</x-responsive-nav-link>
      @endauth

      {{-- Theme quick in mobile --}}
      <div class="px-4 pt-2">
        <div class="inline-flex rounded-full border px-1 py-1 text-xs dark:border-gray-700">
          <button class="rounded-full px-2 py-1" onclick="setTheme('light')">Light</button>
          <button class="rounded-full px-2 py-1" onclick="setTheme('dark')">Dark</button>
          <button class="rounded-full px-2 py-1" onclick="setTheme('system')">Auto</button>
        </div>
      </div>
    </div>
  </div>
</nav>
