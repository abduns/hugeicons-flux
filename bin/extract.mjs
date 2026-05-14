#!/usr/bin/env node
// Hugeicons icon extractor for abduns/hugeicons-flux.
//
// Reads a JSON request from stdin:
//   { "packages": { "<style>": "<absolute package dir>", ... },
//     "icons":    ["<ModuleBasename>", ...] }
//
// Dynamically imports each Hugeicons icon module (they are plain ESM modules
// shipped by @hugeicons/core-free-icons and @hugeicons-pro/core-*) and writes a
// JSON map to stdout:
//   { "<ModuleBasename>": { "<style>": [["path", { ...attrs }], ...] } }
//
// Styles that do not contain a given icon are simply omitted from its entry.

import path from 'node:path';
import { pathToFileURL } from 'node:url';

async function readStdin() {
    const chunks = [];
    for await (const chunk of process.stdin) {
        chunks.push(chunk);
    }
    return Buffer.concat(chunks).toString('utf8');
}

try {
    const { packages, icons } = JSON.parse(await readStdin());
    const result = {};

    for (const module of icons) {
        const styles = {};

        for (const [style, packageDir] of Object.entries(packages)) {
            const file = path.join(packageDir, 'dist', 'esm', `${module}.js`);

            try {
                const imported = await import(pathToFileURL(file).href);

                if (Array.isArray(imported.default)) {
                    styles[style] = imported.default;
                }
            } catch {
                // Icon is not part of this style package — skip it silently.
            }
        }

        result[module] = styles;
    }

    process.stdout.write(JSON.stringify(result));
} catch (error) {
    process.stderr.write(String(error && error.stack ? error.stack : error));
    process.exit(1);
}
