import { defineConfig } from "vite";
import tailwindcss from "@tailwindcss/vite";
import { fileURLToPath, URL } from "node:url";

export default defineConfig(({ command }) => ({
    resolve: {
        alias: {
            "@": fileURLToPath(new URL("./resources/js", import.meta.url)),
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
                main: "resources/js/main.js",
                admin: "resources/js/admin.js",
                "admin-style": "resources/css/app.css",
            },
        },
    },

    server: {
        origin: "http://localhost:3000",
        strictPort: true,
        cors: true,
    },
}));
