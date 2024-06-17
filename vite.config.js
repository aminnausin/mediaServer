import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue'
export default defineConfig({
    // server: {
    //     port: 2771,
    // },
    // server: {
    //     port: 2771,
    //     hmr: {
    //         host: '99.226.252.66',
    //     }
    // },
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        vue(),
    ],
});
