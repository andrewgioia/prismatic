const { defineConfig } = require("vite");

module.exports = defineConfig({
    build: {
        assetsInlineLimit: 0,
        rollupOptions: {
            output: {
                assetFileNames: (asset) => {
                    const source = asset.originalFileNames?.[0]?.replaceAll("\\", "/");

                    if (asset.name?.endsWith(".css")) {
                        return "css/main.css";
                    }

                    if (source?.startsWith("src/")) {
                        return source.slice(4);
                    }

                    return "assets/[name][extname]";
                },
                chunkFileNames: "js/[name].js",
                entryFileNames: "js/main.js",
            },
        },
    },
});
