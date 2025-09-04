import vue from '@vitejs/plugin-vue';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';
import { defineConfig } from 'vite';
import vueDevtools from 'vite-plugin-vue-devtools';

// Enable Vue Devtools in development mode
if (process.env.NODE_ENV === 'development') {
    vueDevtools();
}

export default defineConfig(
    {
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
);
