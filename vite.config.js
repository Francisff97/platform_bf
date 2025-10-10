import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
    ],

    build: {
        // ✅ Output più compatto e ottimizzato
        cssMinify: true,
        minify: 'esbuild', // veloce e compatto
        sourcemap: false,

        rollupOptions: {
            output: {
                // ✅ Evita di creare troppi chunk JS separati
                manualChunks: undefined,
            },
        },

        // ✅ File hashati per cache lunga (immutabili)
        assetsDir: 'assets',
        manifest: true,
        outDir: 'public/build',
        emptyOutDir: true,
    },
});