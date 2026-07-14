import { defineConfig } from "vite";
import tailwindcss from "@tailwindcss/vite";

export default defineConfig(({ command }) => ({
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
