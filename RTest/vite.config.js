import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/sass/app.scss',
                'resources/js/app.js',
                'resources/css/app.css',
                'resources/css/global.css',
                'resources/css/agenda.css',
                'resources/css/crud/crud.css',
                'resources/css/layouts/header.css',
                'resources/css/layouts/sidebar.css',
                'resources/css/excel/excel.css',
                'resources/css/auth/forgot_password.css',
                'resources/css/auth/login.css',
                'resources/css/auth/register.css',
                'resources/css/auth/reset_password.css',
                'resources/css/auth/verify_email.css',
            ],
            refresh: true,
        }),
    ],
});
