import {
    PWA_INSTALL_DISMISS_KEY,
    dismissPwaInstallPrompt,
    isIosSafari,
    isStandaloneDisplayMode,
    shouldShowPwaInstallPrompt,
} from '@/shared/lib/pwaInstall';
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';

describe('pwaInstall', () => {
    beforeEach(() => {
        localStorage.clear();
        vi.stubGlobal('matchMedia', (query: string) => ({
            matches: false,
            media: query,
            onchange: null,
            addListener: vi.fn(),
            removeListener: vi.fn(),
            addEventListener: vi.fn(),
            removeEventListener: vi.fn(),
            dispatchEvent: vi.fn(),
        }));
    });

    afterEach(() => {
        vi.unstubAllGlobals();
    });

    it('detects standalone display mode from matchMedia', () => {
        vi.stubGlobal('matchMedia', (query: string) => ({
            matches: query === '(display-mode: standalone)',
            media: query,
            onchange: null,
            addListener: vi.fn(),
            removeListener: vi.fn(),
            addEventListener: vi.fn(),
            removeEventListener: vi.fn(),
            dispatchEvent: vi.fn(),
        }));

        expect(isStandaloneDisplayMode()).toBe(true);
    });

    it('detects iOS Safari standalone via navigator.standalone', () => {
        vi.stubGlobal('navigator', {
            ...navigator,
            standalone: true,
        });

        expect(isStandaloneDisplayMode()).toBe(true);
    });

    it('detects iOS Safari user agents', () => {
        vi.stubGlobal('navigator', {
            userAgent:
                'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Mobile/15E148 Safari/604.1',
            platform: 'iPhone',
            maxTouchPoints: 5,
        });

        expect(isIosSafari()).toBe(true);
    });

    it('does not treat Chrome on iOS as Safari', () => {
        vi.stubGlobal('navigator', {
            userAgent:
                'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/120.0.6099.119 Mobile/15E148 Safari/604.1',
            platform: 'iPhone',
            maxTouchPoints: 5,
        });

        expect(isIosSafari()).toBe(false);
    });

    it('shows install prompt only on iOS Safari when not dismissed', () => {
        vi.stubGlobal('navigator', {
            userAgent:
                'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Mobile/15E148 Safari/604.1',
            platform: 'iPhone',
            maxTouchPoints: 5,
        });

        expect(shouldShowPwaInstallPrompt()).toBe(true);

        dismissPwaInstallPrompt();

        expect(localStorage.getItem(PWA_INSTALL_DISMISS_KEY)).toBe('1');
        expect(shouldShowPwaInstallPrompt()).toBe(false);
    });

    it('hides install prompt when already standalone', () => {
        vi.stubGlobal('navigator', {
            userAgent:
                'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Mobile/15E148 Safari/604.1',
            platform: 'iPhone',
            maxTouchPoints: 5,
            standalone: true,
        });

        expect(shouldShowPwaInstallPrompt()).toBe(false);
    });
});
