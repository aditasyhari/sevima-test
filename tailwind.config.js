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
                sans: ['Plus Jakarta Sans', 'Figtree', ...defaultTheme.fontFamily.sans],
                retro: ['Outfit', 'Plus Jakarta Sans', 'sans-serif'],
            },
            colors: {
                retro: {
                    bg: '#FAF7F0',
                    surface: '#FFFFFF',
                    border: '#1E1E1E',
                    dark: '#18181B',
                    yellow: '#F5B738',
                    'yellow-light': '#FEF3D6',
                    'yellow-dark': '#B45309',
                    green: '#2D7A4D',
                    'green-light': '#E2F1E8',
                    'green-dark': '#1E5E3A',
                    coral: '#D93838',
                    'coral-light': '#FDE8E8',
                    'coral-dark': '#991B1B',
                    blue: '#2563EB',
                    'blue-light': '#EFF6FF',
                },
            },
            boxShadow: {
                retro: '2px 2px 0px 0px #1E1E1E',
                'retro-lg': '4px 4px 0px 0px #1E1E1E',
                'retro-sm': '1.5px 1.5px 0px 0px #1E1E1E',
            },
            borderWidth: {
                '1.5': '1.5px',
            },
        },
    },

    plugins: [forms],
};
