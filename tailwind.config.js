import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
import typography from '@tailwindcss/typography';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './vendor/laravel/jetstream/**/*.blade.php',
        './vendor/livewire/**/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
                display: ['Sora', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                brand: {
                    50: '#EFF6FC',
                    100: '#D6E8F7',
                    200: '#ADD0EE',
                    300: '#7BB3E0',
                    400: '#4590CB',
                    500: '#1E6FAF',
                    600: '#0E5D9D',
                    700: '#004E89',
                    800: '#003F6D',
                    900: '#03304F',
                },
                accent: {
                    50: '#FFF4EC',
                    100: '#FFE4D1',
                    200: '#FFC9A6',
                    300: '#FFA775',
                    400: '#FF8A4B',
                    500: '#FF6B35',
                    600: '#F2541E',
                    700: '#CE4213',
                },
                court: {
                    DEFAULT: '#2E8B57',
                },
            },
            boxShadow: {
                card: '0 8px 25px rgba(0, 78, 137, 0.12)',
                'card-hover': '0 18px 40px rgba(3, 48, 79, 0.18)',
            },
            borderRadius: {
                '2xl': '1.25rem',
            },
        },
    },

    plugins: [forms, typography],
};