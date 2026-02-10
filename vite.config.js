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
        host: '0.0.0.0',
        port: 5173,
        strictPort: true,
        // HABILITAMOS CORS PARA QUE EL PUERTO 8000 PUEDA LEER LOS ARCHIVOS
        cors: true, 
        hmr: {
            host: '192.168.1.66',
        },
    },
});