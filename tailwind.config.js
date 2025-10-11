import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/**/*.js',
  './resources/**/*.vue',
    ],

    theme: {
        extend: {
      fontFamily: {
        // usa Inter come "sans"
        sans: ['Inter', 'system-ui', '-apple-system', 'Segoe UI', 'Roboto', 'Helvetica Neue', 'Arial', 'Noto Sans', 'Liberation Sans', 'sans-serif'],
        display: ['Inter', 'sans-serif'],
      },
    },

    },

    plugins: [forms],
};
