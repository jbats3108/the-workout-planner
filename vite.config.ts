import inertia from '@inertiajs/vite';
import tailwindcss from '@tailwindcss/vite';
import vue from '@vitejs/plugin-vue';
import laravel from 'laravel-vite-plugin';
import { defineConfig, loadEnv, type Plugin } from 'vite';
import { VitePWA } from 'vite-plugin-pwa';
import vueDevtools from 'vite-plugin-vue-devtools';
import { detectLanIpv4 } from './vite/detectLanHost';

/**
 * @inertiajs/vite SSR dev mode injects CSS via resolvedUrls.local (localhost), ignoring
 * server.origin. Rewrite local to the LAN origin so phones can load SSR stylesheets.
 */
function lanSsrDevOrigin(publicHost: string): Plugin {
    return {
        name: 'lan-ssr-dev-origin',
        configureServer(server) {
            server.httpServer?.once('listening', () => {
                const origin = server.config.server.origin?.replace(/\/$/, '');

                if (!origin || !server.resolvedUrls?.local?.length) {
                    return;
                }

                const publicUrl = new URL(server.config.base, `${origin}/`).href;
                server.resolvedUrls.local[0] = publicUrl;
                if (server.resolvedUrls.network?.length) {
                    server.resolvedUrls.network[0] = publicUrl;
                }

                server.config.logger.info(`LAN dev: phone → http://${publicHost}:8000 · Vite → http://${publicHost}:5173`);
            });
        },
    };
}

export default defineConfig(({ mode }) => {
    const env = loadEnv(mode, process.cwd(), '');
    const ddevUrl = env.DDEV_PRIMARY_URL || null;
    const lanDev = env.LAN_DEV === '1' || env.LAN_DEV === 'true';
    // Phone / LAN: composer run dev:lan auto-detects wifi/ethernet (skips docker).
    // Optional override: VITE_DEV_HOST=192.168.x.x when detection is wrong.
    const lanHost = env.VITE_DEV_HOST || (lanDev ? detectLanIpv4() : null);

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
            VitePWA({
                registerType: 'autoUpdate',
                injectRegister: false,
                manifest: false,
                workbox: {
                    globPatterns: ['**/*.{js,css,ico,png,svg,woff2,webmanifest}'],
                    navigateFallback: null,
                    cleanupOutdatedCaches: true,
                },
                devOptions: {
                    enabled: false,
                },
            }),
            ...(shareOnLan ? [lanSsrDevOrigin(publicHost!)] : []),
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
