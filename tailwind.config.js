import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                // TUS COLORES PERSONALIZADOS
                aromas: {
                    // Primarios
                    main: '#22272E',      // Fondo Principal
                    secondary: '#394049', // Tarjetas / Paneles
                    tertiary: '#646F7B',  // Bordes / Detalles sutiles
                    highlight: '#FDC974', // Destacado (Texto activo, iconos)
                    
                    // Interactivos (Estados)
                    success: '#3AA580',   // Correcto
                    error: '#D24749',     // Crítico
                    warning: '#FBC02D',   // Alerta
                    info: '#2E84F2',      // Informativo
                }
            },
        },
    },

    plugins: [forms],
};