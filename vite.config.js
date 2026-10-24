import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";

export default defineConfig({
    plugins: [
        laravel({
            input: ["resources/css/app.css", "resources/js/app.js", "resources/js/qudrat-admin.js", "resources/js/qudrat-student.js", "resources/js/arabic-skills-student.js"],
            refresh: true,
        }),
    ],
});
