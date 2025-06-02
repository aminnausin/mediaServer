// import { fileURLToPath, URL } from 'node:url';
import { defineConfig, loadEnv } from 'vite';

import viteCompression from 'vite-plugin-compression';
import laravel from 'laravel-vite-plugin';
import Icons from 'unplugin-icons/vite';
import vue from '@vitejs/plugin-vue';
import { fileURLToPath } from 'node:url';

export default defineConfig(({ mode }) => {
    const env = loadEnv(mode, process.cwd());

    return {
        mode: env.APP_ENV === 'local' ? 'development' : 'production',
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
    };
});
