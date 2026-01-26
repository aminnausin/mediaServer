import { defineConfig, loadEnv } from 'vite';
import { fileURLToPath } from 'node:url';

import viteCompression from 'vite-plugin-compression';
import laravel from 'laravel-vite-plugin';
import Icons from 'unplugin-icons/vite';
import vue from '@vitejs/plugin-vue';

const env = loadEnv('.env', process.cwd());

export default defineConfig({
    mode: env.VITE_APP_ENV === 'local' ? 'development' : 'production',
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
            compiler: 'vue3',
            autoInstall: true,
        }),
        viteCompression(),
    ],
    resolve: {
        alias: {
            '@': fileURLToPath(new URL('./resources/js', import.meta.url)),
            vue: 'vue/dist/vue.esm-bundler.js',
        },
    },
    server: {
        host: true,
        cors: {
            origin: '*',
        },
        hmr: {
            host: env.VITE_APP_HOST,
        },
        watch: {
            ignored: ['**/.git/**', '**/storage/**', '**/bootstrap/cache/**', '**/vendor/**', '**/coverage/**', '.phpunit.result.cache'],
        },
    },
});
