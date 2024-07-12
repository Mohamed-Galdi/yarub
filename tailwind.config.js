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
                hacen: ["hacen"],
                nitaqat: ["nitaqat"],
                judur: ["judur"],
                arabic_handwrite: ["arabic_handwrite"],
            },
            colors: {
                primary: "#292a65",
                pr: {
                    50: "#9495b2",
                    100: "#7f7fa3",
                    200: "#696a93",
                    300: "#545584",
                    400: "#3e3f74",
                    500: "#292a65",
                    600: "#25265b",
                    700: "#212251",
                    800: "#1d1d47",
                    900: "#19193d",
                    950: "#151533",
                },
            },
        },
    },

    plugins: [
        require("flowbite/plugin")({
            charts: true,
        }),
        forms,
    ],
};
