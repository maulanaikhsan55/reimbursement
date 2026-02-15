import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css', 
                'resources/css/landing.css', 
                'resources/css/dashboard/finance.css',
                'resources/css/dashboard-ultra.css',
                'resources/css/modules/pengajuan-detail.css',
                'resources/css/pages/pegawai/dashboard.css',
                'resources/css/pages/pegawai/pengajuan.css',
                'resources/css/pages/pegawai/profile.css',
                'resources/css/pages/pegawai/notifikasi.css',
                'resources/css/pages/pegawai/responsive-fixes.css',
                'resources/css/pages/atasan/dashboard.css',
                'resources/js/app.js',
                'resources/js/finance/finance.js',
                'resources/js/dashboard-ultra.js'
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
