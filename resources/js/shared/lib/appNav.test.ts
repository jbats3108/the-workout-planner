import { isPathActive, isSettingsActive, primaryNavItems, settingsNavItems } from '@/shared/lib/appNav';
import { describe, expect, it } from 'vitest';

const route = (name: string) => `/${name}`;

describe('isPathActive', () => {
    it('matches exact paths and nested routes', () => {
        expect(isPathActive('/dashboard', '/dashboard')).toBe(true);
        expect(isPathActive('/dashboard/stats', '/dashboard')).toBe(true);
        expect(isPathActive('/history', '/dashboard')).toBe(false);
    });
});

describe('isSettingsActive', () => {
    it('matches profile and appearance settings paths', () => {
        expect(isSettingsActive('/settings/profile')).toBe(true);
        expect(isSettingsActive('/settings/appearance')).toBe(true);
        expect(isSettingsActive('/settings/training')).toBe(false);
    });
});

describe('primaryNavItems', () => {
    it('includes admin link when user is admin', () => {
        const links = primaryNavItems(route, { isAdmin: true });
        expect(links.map((link) => link.label)).toEqual(['Dashboard', 'History', 'Training', 'Admin']);
    });

    it('omits admin link for non-admin users', () => {
        const links = primaryNavItems(route, { isAdmin: false });
        expect(links.map((link) => link.label)).toEqual(['Dashboard', 'History', 'Training']);
    });
});

describe('settingsNavItems', () => {
    it('includes admin link when requested', () => {
        const links = settingsNavItems(route, { isAdmin: true });
        expect(links.map((link) => link.label)).toEqual(['Profile', 'Appearance', 'Admin']);
    });

    it('returns profile and appearance by default', () => {
        const links = settingsNavItems(route);
        expect(links.map((link) => link.label)).toEqual(['Profile', 'Appearance']);
    });
});
