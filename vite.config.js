// vite.config.js
import { defineConfig } from 'vite'
import laravel from 'laravel-vite-plugin'

export default defineConfig({
  plugins: [
    laravel({
      input: ['resources/css/app.css', 'resources/js/app.js'],
      refresh: true,
      buildDirectory: 'build',
    }),
  ],
  build: {
    outDir: 'public/build',
    assetsDir: '',          // niente sottocartella
    manifest: false,        // ⬅️ disattiva manifest
    emptyOutDir: true,
    rollupOptions: {
      output: {
        // nomi deterministici, senza hash
        entryFileNames: 'app.js',
        chunkFileNames: '[name].js',
        assetFileNames: (assetInfo) => {
          // assicura "app.css" per l’estrazione CSS
          if (assetInfo.name && assetInfo.name.endsWith('.css')) return 'app.css'
          return '[name][extname]'
        },
      },
    },
  },
})