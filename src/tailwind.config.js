import defaultTheme from "tailwindcss/defaultTheme";
import forms from "@tailwindcss/forms";

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        "./vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php",
        "./storage/framework/views/*.php",
        "./resources/views/**/*.blade.php",
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ["Figtree", ...defaultTheme.fontFamily.sans],
            },
            colors: {
                brand: {
                    50: '#FFF9EB',
                    100: '#FFF0CC',
                    200: '#FFE3A0',
                    300: '#FFD16D',
                    400: '#F5BB4A',
                    500: '#DBA22E',
                    600: '#B8871A',
                    700: '#946C0E',
                    800: '#705104',
                    900: '#4C3600',
                },
                sky: {
                    50: '#F0F8FA',
                    100: '#DAEEF4',
                    200: '#C3E4ED',
                    300: '#AAD6E2',
                    400: '#8FC4D4',
                    500: '#74B2C4',
                    600: '#599FB4',
                    700: '#3E8DA4',
                    800: '#237B94',
                    900: '#086984',
                },
                blush: {
                    50: '#FFF0F4',
                    100: '#FDE4EB',
                    200: '#FCD2DF',
                    300: '#F2B0C4',
                    400: '#E88EA9',
                    500: '#DE6C8E',
                    600: '#D44A73',
                    700: '#CA2858',
                    800: '#B81647',
                    900: '#A00436',
                },
            },
            boxShadow: {
                sm: "0 1px 2px 0 rgba(0, 0, 0, 0.05)",
                md: "0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06)",
                lg: "0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05)",
                xl: "0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04)",
                "brand-glow": "0 0 20px rgba(255, 209, 109, 0.3)",
                "sky-glow": "0 0 20px rgba(170, 214, 226, 0.3)",
            },
            transitionDuration: {
                200: "200ms",
                300: "300ms",
            },
        },
    },

    plugins: [forms],
};
