<button type="button"
        x-data
        x-on:click="
          const c = document.documentElement.classList;
          const isDark = c.toggle('dark');
          localStorage.setItem('theme', isDark ? 'dark' : 'light');
        "
        aria-label="Toggle theme"
        class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-gray-200 bg-white
               text-gray-700 hover:bg-gray-50
               dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 dark:hover:bg-gray-800">
    {{-- Sun / Moon icons (lucide) --}}
    <svg class="h-5 w-5 dark:hidden" viewBox="0 0 24 24" fill="none" stroke="currentColor">
        <circle cx="12" cy="12" r="4" />
        <path d="M12 2v2M12 20v2M20 12h2M2 12H4M17 17l1.5 1.5M5.5 5.5L7 7M17 7l1.5-1.5M5.5 18.5L7 17"/>
    </svg>
    <svg class="hidden h-5 w-5 dark:block" viewBox="0 0 24 24" fill="none" stroke="currentColor">
        <path d="M21 12.79A9 9 0 1 1 11.21 3
                 7 7 0 0 0 21 12.79z"/>
    </svg>
</button>