import inertia from '@inertiajs/vite';
import vue from '@vitejs/plugin-vue';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';
import { defineConfig, loadEnv, type Plugin } from 'vite';
import vueDevtools from 'vite-plugin-vue-devtools';

/**
 * @inertiajs/vite SSR dev mode injects CSS via resolvedUrls.local (localhost), ignoring
 * server.origin. Rewrite local to the LAN origin so phones can load SSR stylesheets.
 */
function lanSsrDevOrigin(): Plugin {
    return {
        name: 'lan-ssr-dev-origin',
        configureServer(server) {
            server.httpServer?.once('listening', () => {
                const origin = server.config.server.origin?.replace(/\/$/, '');

                if (!origin || !server.resolvedUrls?.local?.length) {
                    return;
                }

                server.resolvedUrls.local[0] = new URL(server.config.base, `${origin}/`).href;
            });
        },
    };
}

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
            ...(shareOnLan ? [lanSsrDevOrigin()] : []),
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
