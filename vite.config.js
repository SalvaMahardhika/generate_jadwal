import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';
import vue from '@vitejs/plugin-vue';
import dns from 'dns';

// Memaksa Node.js mengutamakan IPv4 agar Vite tidak stuck berdetik-detik saat lookup DNS IPv6 di Windows
dns.setDefaultResultOrder('ipv4first');

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.ts'],
            refresh: true,
        }),
        tailwindcss(),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
    ],
    server: {
        host: '127.0.0.1',
        port: 5173,
        strictPort: true, // Mencegah Vite diam-diam pindah ke port 5174 jika port 5173 masih terunci
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});