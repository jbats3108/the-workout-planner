import '../css/app.css';

import { createInertiaApp } from '@inertiajs/vue3';
import { ZiggyVue } from 'ziggy-js';
import { initializeTheme } from './composables/useAppearance';
import type { AppPageProps } from './types';

const appName = import.meta.env.VITE_APP_NAME || 'OVRLOAD';

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    pages: './pages',
    withApp(app, { ssr, page }) {
        if (ssr) {
            const ziggy = (page.props as AppPageProps).ziggy;

            app.use(ZiggyVue, {
                ...ziggy,
                location: new URL(ziggy.location),
            });

            return;
        }

        app.use(ZiggyVue);
    },
    progress: {
        color: '#c8ff00',
    },
});

initializeTheme();
