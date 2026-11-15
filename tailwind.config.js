import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.js',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
                display: ['Sora', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                night: {
                    950: '#070B16',
                    900: '#0B1120',
                    850: '#0E1526',
                    800: '#111827',
                    700: '#172033',
                    600: '#1F2937',
                },
                brand: {
                    400: '#60A5FA',
                    500: '#3B82F6',
                    600: '#2563EB',
                    700: '#1D4ED8',
                },
            },
            boxShadow: {
                glow: '0 0 0 1px rgba(59,130,246,.25), 0 20px 45px -20px rgba(37,99,235,.55)',
                card: '0 24px 60px -32px rgba(2,6,23,.9)',
            },
            backgroundImage: {
                'hero-fade': 'linear-gradient(180deg, rgba(7,11,22,.15) 0%, rgba(7,11,22,.65) 55%, #070B16 100%)',
                'grid-lines': 'radial-gradient(circle at 1px 1px, rgba(148,163,184,.09) 1px, transparent 0)',
            },
            keyframes: {
                'fade-up': {
                    '0%': { opacity: 0, transform: 'translateY(12px)' },
                    '100%': { opacity: 1, transform: 'translateY(0)' },
                },
                shimmer: {
                    '100%': { transform: 'translateX(100%)' },
                },
            },
            animation: {
                'fade-up': 'fade-up .5s cubic-bezier(.21,.61,.35,1) both',
                shimmer: 'shimmer 1.6s infinite',
            },
        },
    },

    plugins: [forms],
};
