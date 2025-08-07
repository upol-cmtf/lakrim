import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/sass/web/app.scss', // Web assets entry point
                'resources/js/web/app.js',
                'resources/sass/admin/app.scss', // Admin assets entry point
                'resources/js/admin/app.js',
            ],
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
    ],
    // server: {
    //     host: '127.0.0.1',
    //     port: 5173,
    //     cors: {
    //         origin: 'http://lakrim.upol',
    //         credentials: true,
    //     },
    //     hmr: {
    //         host: 'lakrim.upol',
    //     },
    // },
    resolve: {
        alias: {
            vue: 'vue/dist/vue.esm-bundler.js',
        },
    },
});
