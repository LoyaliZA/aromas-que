import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
            ],
            refresh: true,
        }),
    ],
    server: {
        host: '0.0.0.0', // Esto permite que escuche en Tailscale Y en Local
        port: 5173,
        strictPort: true,
        cors: true, 
        hmr: {
            host: '100.75.11.59',
            // Al quitarla, el navegador usará la IP que esté en la barra de direcciones
        },
    },
});