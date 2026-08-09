import { defineConfig } from "vite";
import tailwindcss from "@tailwindcss/vite";
import { fileURLToPath, URL } from "node:url";

export default defineConfig(({ command }) => ({
    resolve: {
        alias: {
            // Старият alias остава непроменен.
            "@": fileURLToPath(new URL("./resources/js", import.meta.url)),

            // Новата обща Admin UI архитектура.
            "@admin-ui": fileURLToPath(
                new URL("./resources/ui/admin", import.meta.url),
            ),

            // Ресурси, специфични за отделните Features.
            "@features": fileURLToPath(
                new URL("./app/Features", import.meta.url),
            ),
        },
    },

    base: command === "build" ? "/public/dist/" : "/",

    plugins: [tailwindcss()],

    build: {
        outDir: "public/dist",
        emptyOutDir: true,
        manifest: true,

        rollupOptions: {
            input: {
                // Съществуващите entries.
                main: "resources/js/main.js",
                admin: "resources/js/admin.js",
                "admin-style": "resources/css/app.css",

                // Новият изолиран Admin UI.
                "admin-ui": "resources/ui/admin/index.js",

                "admin-ui-style": "resources/ui/admin/styles/index.css",
            },
        },
    },

    server: {
        origin: "http://localhost:3000",
        strictPort: true,
        cors: true,
    },
}));