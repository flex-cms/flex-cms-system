import { defineConfig } from "vite";
import tailwindcss from "@tailwindcss/vite";

import { fileURLToPath, URL } from "node:url";

import { existsSync, readdirSync } from "node:fs";

import { extname, join, relative } from "node:path";

function discoverFeatureAssets() {
    const projectRoot = fileURLToPath(new URL(".", import.meta.url));

    const featuresRoot = join(projectRoot, "app", "Features");

    const inputs = {};

    if (!existsSync(featuresRoot)) {
        return inputs;
    }

    const walk = (directory) => {
        for (const entry of readdirSync(directory, {
            withFileTypes: true,
        })) {
            const absolutePath = join(directory, entry.name);

            if (entry.isDirectory()) {
                walk(absolutePath);

                continue;
            }

            const extension = extname(entry.name);

            if (extension !== ".js" && extension !== ".css") {
                continue;
            }

            const normalized = absolutePath.replaceAll("\\", "/");

            if (!normalized.includes("/Resources/js/") && !normalized.includes("/Resources/css/")) {
                continue;
            }

            const sourcePath = relative(projectRoot, absolutePath).replaceAll("\\", "/");

            const entryName = sourcePath
                .replace(/\.(js|css)$/, "")
                .replaceAll("/", "-")
                .toLowerCase();

            inputs[entryName] = sourcePath;
        }
    };

    walk(featuresRoot);

    return inputs;
}

export default defineConfig(({ command }) => {
    const featureAssets = discoverFeatureAssets();

    return {
        resolve: {
            alias: {
                "@": fileURLToPath(new URL("./resources/js", import.meta.url)),

                "@admin-ui": fileURLToPath(new URL("./resources/ui/admin", import.meta.url)),

                "@features": fileURLToPath(new URL("./app/Features", import.meta.url)),
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

                    "admin-ui": "resources/ui/admin/index.js",

                    "admin-ui-style": "resources/ui/admin/styles/index.css",

                    ...featureAssets,
                },
            },
        },

        server: {
            origin: "http://localhost:3000",

            strictPort: true,

            cors: true,
        },
    };
});
