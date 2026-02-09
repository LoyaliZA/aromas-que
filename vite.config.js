import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        // 1. Esto permite que Docker exponga el servidor a la red interna
        host: '0.0.0.0', 
        
        // 2. Aseguramos el puerto (debe coincidir con docker-compose)
        port: 5173, 
        
        // 3. CRÍTICO: Configuración HMR (Hot Module Replacement)
        // Esto le dice a tu navegador en Windows: "Oye, los cambios de CSS/JS
        // búscalos en esta IP, no en localhost".
        hmr: {
            host: '192.168.1.66', 
        },
        
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});