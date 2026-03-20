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
                purple: {
                    50: "#faf5ff",
                    100: "#f3e8ff",
                    400: "#d8b4fe",
                    500: "#a855f7",
                    600: "#9333ea",
                    700: "#7e22ce",
                    800: "#6b21a8",
                    900: "#581c87",
                },
                pink: {
                    400: "#f472b6",
                    500: "#ec4899",
                    600: "#db2777",
                    700: "#be185d",
                },
            },
            gradientColorStops: {
                "purple-start": "#9333ea",
                "pink-end": "#ec4899",
            },
            boxShadow: {
                sm: "0 1px 2px 0 rgba(0, 0, 0, 0.05)",
                md: "0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06)",
                lg: "0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05)",
                xl: "0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04)",
                "purple-glow": "0 0 20px rgba(147, 51, 234, 0.3)",
                "pink-glow": "0 0 20px rgba(236, 72, 153, 0.3)",
            },
            transitionDuration: {
                200: "200ms",
                300: "300ms",
            },
        },
    },

    plugins: [forms],
};
