import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.{jsx,js}',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['"Plus Jakarta Sans"', 'Inter', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                green: {
                    50: '#F1F9F5',
                    100: '#DDF0E6',
                    500: '#1E8A5E',
                    600: '#14704D',
                    700: '#0F5A3F',
                    900: '#0B3B2C',
                },
                gold: {
                    100: '#F7EFD0',
                    300: '#E3CB76',
                    500: '#C9A227',
                    600: '#B68A1B',
                },
                surface: '#FAFAF6',
                border: '#E6E4D9',
                text: '#10211A',
                muted: '#5E6E66',
                danger: '#C0392B',
                info: '#2F6F9F',
            },
            borderRadius: {
                btn: '12px',
                card: '16px',
                hero: '24px',
            },
        },
    },

    plugins: [forms],
};
