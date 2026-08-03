export type ZiggyRouteFn = (name: string, params?: Record<string, unknown>, absolute?: boolean) => string;

export type AppNavLink = {
    href: string;
    label: string;
    match: string;
};

export function isPathActive(path: string, match: string): boolean {
    return path === match || path.startsWith(`${match}/`);
}

export function isSettingsActive(path: string): boolean {
    return path.startsWith('/settings/profile') || path.startsWith('/settings/appearance');
}

export function primaryNavItems(route: ZiggyRouteFn, { isAdmin }: { isAdmin: boolean }): AppNavLink[] {
    const links: AppNavLink[] = [
        { href: route('dashboard'), label: 'Dashboard', match: '/dashboard' },
        { href: route('history.index'), label: 'History', match: '/history' },
        { href: route('training.edit'), label: 'Training', match: '/settings/training' },
    ];

    if (isAdmin) {
        links.push({ href: route('admin.index'), label: 'Admin', match: '/admin' });
    }

    return links;
}

export function settingsNavItems(route: ZiggyRouteFn, { isAdmin = false }: { isAdmin?: boolean } = {}): AppNavLink[] {
    const items: AppNavLink[] = [
        { href: route('profile.edit'), label: 'Profile', match: '/settings/profile' },
        { href: route('appearance'), label: 'Appearance', match: '/settings/appearance' },
    ];

    if (isAdmin) {
        items.push({ href: route('admin.index'), label: 'Admin', match: '/admin' });
    }

    return items;
}
