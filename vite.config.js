import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";

export default defineConfig({
    plugins: [
        laravel({
            input: ["resources/css/app.css", "resources/css/admin-users.css", "resources/js/users-admin.js", "resources/js/app.js", "resources/js/qudrat-admin.js", "resources/js/qudrat-student.js", "resources/js/arabic-skills-student.js", "resources/js/learning-public.js", "resources/css/videos-admin.css", "resources/js/videos-admin.js", "resources/css/videos-student.css", "resources/js/videos-student.js", "resources/js/videos-player.js", "resources/css/videos-public.css", "resources/js/videos-public.js"],
            refresh: true,
        }),
    ],
});
