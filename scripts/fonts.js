const { copyFileSync, mkdirSync } = require("node:fs");
const { join } = require("node:path");

const root = join(__dirname, "..");
const source = join(root, "node_modules", "hack-font", "build", "web", "fonts");
const target = join(root, "src", "fonts", "hack");
const files = [
    "hack-regular-subset.woff2",
    "hack-regular-subset.woff",
    "hack-italic-subset.woff2",
    "hack-italic-subset.woff",
    "hack-bold-subset.woff2",
    "hack-bold-subset.woff",
    "hack-bolditalic-subset.woff2",
    "hack-bolditalic-subset.woff",
];

mkdirSync(target, { recursive: true });

for (const file of files) {
    copyFileSync(join(source, file), join(target, file));
}

copyFileSync(
    join(root, "node_modules", "hack-font", "LICENSE.md"),
    join(target, "LICENSE.md"),
);
