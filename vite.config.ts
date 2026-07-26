import inertia from '@inertiajs/vite';
import vue from '@vitejs/plugin-vue';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';
import { defineConfig, loadEnv } from 'vite';
import vueDevtools from 'vite-plugin-vue-devtools';

export default defineConfig(({ mode }) => {
    const env = loadEnv(mode, process.cwd(), '');
    const ddevUrl = env.DDEV_PRIMARY_URL || null;
    const lanHost = env.VITE_DEV_HOST || null;

    // Phone / LAN: set VITE_DEV_HOST to this machine's LAN IP (e.g. 192.168.0.131).
    // DDEV: DDEV_PRIMARY_URL is set automatically.
    const publicHost = lanHost || (ddevUrl ? new URL(ddevUrl).hostname : null);
    const shareOnLan = Boolean(publicHost);

    return {
        plugins: [
            laravel({
                input: ['resources/js/app.ts'],
                refresh: true,
            }),
            inertia({
                ssr: {
                    cluster: true,
                    host: '127.0.0.1',
                },
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
        server: shareOnLan
            ? {
                  host: '0.0.0.0',
                  port: 5173,
                  strictPort: true,
                  origin: `http://${publicHost}:5173`,
                  hmr: {
                      host: publicHost,
                  },
                  cors: {
                      origin: true,
                  },
              }
            : undefined,
    };
});
