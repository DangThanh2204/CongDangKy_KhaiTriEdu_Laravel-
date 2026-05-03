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
                'resources/css/pages/home.css',
                'resources/css/pages/courses/show.css',
                'resources/css/pages/courses/intakes.css',
                'resources/css/pages/student/dashboard.css',
                'resources/css/pages/student/application-status.css',
                'resources/css/pages/student/payment-history.css',
                'resources/css/pages/instructor/dashboard.css',
                'resources/css/pages/admin/dashboard.css',
                'resources/css/pages/admin/settings.css',
                'resources/css/pages/admin/users.css',
                'resources/css/pages/admin/backups.css',
                'resources/js/app.js',
                'resources/js/custom.js',
            ],
            refresh: true,
        }),
    ],
});
