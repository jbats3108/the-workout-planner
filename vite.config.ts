import vue from '@vitejs/plugin-vue';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';
import { defineConfig } from 'vite';
import vueDevtools from 'vite-plugin-vue-devtools';

// Enable Vue Devtools in development mode
if (process.env.NODE_ENV === 'development') {
    vueDevtools();
}

const ddevUrl = process.env.DDEV_PRIMARY_URL ?? null

// Configure to work with ddev
const server = {
    // Respond to all network requests
    host: "0.0.0.0",
        port: 5173,
        strictPort: true,

        origin: ddevUrl ? `${ddevUrl.replace(/:\d+$/, "")}:5173` : null,

    // 👇👇👇👇👇👇👇👇👇👇👇👇👇👇👇👇👇👇👇👇👇👇👇👇👇👇👇👇👇👇👇👇👇👇
    // Configure CORS securely for the Vite dev server to allow requests from *.ddev.site domains,
    // supports additional hostnames (via regex). If you use another `project_tld`, adjust this.
        cors: {
        origin: /https?:\/\/([A-Za-z0-9\-.]+)?(\.ddev\.site)(?::\d+)?$/,
    },
}

const config = {
    plugins: [
        laravel({
            input: ['resources/js/app.ts'],
            ssr: 'resources/js/ssr.ts',
            refresh: true,
        }),
        tailwindcss(),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
        vueDevtools(),
    ],
}

if (ddevUrl){
    config.server = server
}

export default defineConfig(
    config
);
