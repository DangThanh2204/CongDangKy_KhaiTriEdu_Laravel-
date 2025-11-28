import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    server: {
        fs: {
            strict: true,
            allow: ['resources', 'public'], 
        },
    },
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/css/admin.css',
                'resources/js/app.js',
                'resources/js/custom.js',
            ],
            refresh: true,
        }),
    ],
});