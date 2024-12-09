// import { fileURLToPath, URL } from 'node:url';
import { defineConfig } from 'vite';

import laravel from 'laravel-vite-plugin';
import Icons from 'unplugin-icons/vite';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.ts'],
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
        Icons({
            // experimental
            autoInstall: true,
        }),
    ],
    resolve: {
        alias: {
            vue: 'vue/dist/vue.esm-bundler.js',
            // '@': fileURLToPath(new URL('./src', import.meta.url)),
        },
    },
});
