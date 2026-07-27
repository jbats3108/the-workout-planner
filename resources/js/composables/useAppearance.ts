import { onMounted, ref } from 'vue';

type Appearance = 'light' | 'dark' | 'system';
type ResolvedTheme = 'light' | 'dark';

const FAVICON = {
    light: '/favicon-light.svg',
    dark: '/favicon-dark.svg',
} as const;

function resolveTheme(value: Appearance): ResolvedTheme {
    if (value === 'system') {
        return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
    }

    return value;
}

/**
 * Browsers cache favicons aggressively and often ignore href updates.
 * Remove existing icon links and insert a fresh one with a cache-busting query.
 */
function updateFavicon(theme: ResolvedTheme) {
    if (typeof document === 'undefined') {
        return;
    }

    document.querySelectorAll('link[rel="icon"], link[rel="shortcut icon"]').forEach((el) => el.remove());

    const link = document.createElement('link');
    link.rel = 'icon';
    link.type = 'image/svg+xml';
    link.dataset.appFavicon = '';
    // Cache-bust so Chrome actually swaps the tab icon
    link.href = `${FAVICON[theme]}?theme=${theme}`;
    document.head.appendChild(link);
}

export function updateTheme(value: Appearance) {
    if (typeof window === 'undefined') {
        return;
    }

    const theme = resolveTheme(value);

    document.documentElement.classList.toggle('dark', theme === 'dark');
    updateFavicon(theme);
}

const setCookie = (name: string, value: string, days = 365) => {
    if (typeof document === 'undefined') {
        return;
    }

    const maxAge = days * 24 * 60 * 60;

    document.cookie = `${name}=${value};path=/;max-age=${maxAge};SameSite=Lax`;
};

const mediaQuery = () => {
    if (typeof window === 'undefined') {
        return null;
    }

    return window.matchMedia('(prefers-color-scheme: dark)');
};

const getStoredAppearance = () => {
    if (typeof window === 'undefined') {
        return null;
    }

    return localStorage.getItem('appearance') as Appearance | null;
};

const handleSystemThemeChange = () => {
    const currentAppearance = getStoredAppearance();

    updateTheme(currentAppearance || 'system');
};

export function initializeTheme() {
    if (typeof window === 'undefined') {
        return;
    }

    // Initialize theme from saved preference or default to dark (brand: dark-first)...
    const savedAppearance = getStoredAppearance();
    updateTheme(savedAppearance || 'dark');

    // Set up system theme change listener...
    mediaQuery()?.addEventListener('change', handleSystemThemeChange);
}

const appearance = ref<Appearance>('dark');

export function useAppearance() {
    onMounted(() => {
        const savedAppearance = localStorage.getItem('appearance') as Appearance | null;

        if (savedAppearance) {
            appearance.value = savedAppearance;
        }
    });

    function updateAppearance(value: Appearance) {
        appearance.value = value;

        // Store in localStorage for client-side persistence...
        localStorage.setItem('appearance', value);

        // Store in cookie for SSR...
        setCookie('appearance', value);

        updateTheme(value);
    }

    return {
        appearance,
        updateAppearance,
    };
}
