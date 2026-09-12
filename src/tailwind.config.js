import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        "./vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php",
        "./storage/framework/views/*.php",
        "./resources/views/**/*.blade.php",
        "./resources/js/react/**/*.{js,jsx}",
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                primary: {
                    DEFAULT: '#16A34A',
                    light: '#DCFCE7',
                    dark: '#166534',
                    50: '#F0FDF4',
                    100: '#DCFCE7',
                    200: '#BBF7D0',
                    300: '#86EFAC',
                    400: '#4ADE80',
                    500: '#22C55E',
                    600: '#16A34A',
                    700: '#15803D',
                    800: '#166534',
                    900: '#14532D',
                },
                canvas: '#F8FAFC',
                surface: '#FFFFFF',
                ink: '#0F172A',
                border: '#E2E8F0',
                brand: {
                    DEFAULT: '#16A34A',
                    light: '#DCFCE7',
                    dark: '#166534',
                    50: '#F0FDF4',
                    100: '#DCFCE7',
                    200: '#BBF7D0',
                    300: '#86EFAC',
                    400: '#4ADE80',
                    500: '#22C55E',
                    600: '#16A34A',
                    700: '#15803D',
                    800: '#166534',
                    900: '#14532D',
                },
                sky: {
                    DEFAULT: '#64748B',
                    light: '#F1F5F9',
                    dark: '#334155',
                    50: '#F8FAFC',
                    100: '#F1F5F9',
                    200: '#E2E8F0',
                    300: '#CBD5E1',
                    400: '#94A3B8',
                    500: '#64748B',
                    600: '#475569',
                    700: '#334155',
                    800: '#1E293B',
                    900: '#0F172A',
                },
                blush: {
                    DEFAULT: '#94A3B8',
                    light: '#F8FAFC',
                    dark: '#475569',
                    50: '#F8FAFC',
                    100: '#F1F5F9',
                    200: '#E2E8F0',
                    300: '#CBD5E1',
                    400: '#94A3B8',
                    500: '#64748B',
                    600: '#475569',
                    700: '#334155',
                    800: '#1E293B',
                    900: '#0F172A',
                },
            },
            boxShadow: {
                subtle: '0 1px 2px rgb(15 23 42 / 0.06), 0 8px 24px rgb(15 23 42 / 0.04)',
            },
            borderRadius: { control: '8px', card: '12px' },
            spacing: { 4: '1rem', 8: '2rem' },
            transitionDuration: {
                150: '150ms',
                200: '200ms',
                300: '300ms',
            },
        },
    },

    plugins: [forms],
};
