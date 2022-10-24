import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import react from '@vitejs/plugin-react';
import path from 'path';

export default defineConfig({
    plugins: [
        laravel({
            input: 'resources/js/App.tsx',
            ssr: 'resources/js/ssr.tsx',
            refresh: true,
        }),
        react(),
    ],
    ssr: {
        noExternal: ['@inertiajs/server'],
    },

    resolve: {
        alias: {
            "@": path.resolve(__dirname, "./resources/js"),
            ziggy: path.resolve('vendor/tightenco/ziggy/dist'),
        },
    },
    optimizeDeps: {
        include: ["ziggy"],
    },
});
