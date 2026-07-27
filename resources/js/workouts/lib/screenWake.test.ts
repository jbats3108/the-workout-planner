import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import {
    canUseScreenWake,
    primeScreenWake,
    releaseScreenWake,
    requestScreenWake,
    resetScreenWakeForTests,
} from '@/workouts/lib/screenWake';

describe('screenWake', () => {
    beforeEach(() => {
        resetScreenWakeForTests();
        Object.defineProperty(window, 'isSecureContext', {
            configurable: true,
            value: true,
        });
        Object.defineProperty(document, 'visibilityState', {
            configurable: true,
            value: 'visible',
        });
    });

    afterEach(() => {
        resetScreenWakeForTests();
        vi.unstubAllGlobals();
    });

    it('detects wake lock support in a secure context', () => {
        vi.stubGlobal('navigator', { wakeLock: { request: vi.fn() } });
        expect(canUseScreenWake()).toBe(true);
    });

    it('requests a screen wake lock', async () => {
        const release = vi.fn().mockResolvedValue(undefined);
        const request = vi.fn().mockResolvedValue({
            released: false,
            addEventListener: vi.fn(),
            release,
        });
        vi.stubGlobal('navigator', { wakeLock: { request } });

        await expect(requestScreenWake()).resolves.toBe(true);
        expect(request).toHaveBeenCalledWith('screen');
    });

    it('skips wake lock when the page is hidden', async () => {
        Object.defineProperty(document, 'visibilityState', {
            configurable: true,
            value: 'hidden',
        });
        const request = vi.fn();
        vi.stubGlobal('navigator', { wakeLock: { request } });

        await expect(requestScreenWake()).resolves.toBe(false);
        expect(request).not.toHaveBeenCalled();
    });

    it('reuses an active wake lock', async () => {
        const request = vi.fn().mockResolvedValue({
            released: false,
            addEventListener: vi.fn(),
            release: vi.fn(),
        });
        vi.stubGlobal('navigator', { wakeLock: { request } });

        await requestScreenWake();
        await requestScreenWake();

        expect(request).toHaveBeenCalledTimes(1);
    });

    it('primes wake lock from a user gesture helper', () => {
        const request = vi.fn().mockResolvedValue({
            released: false,
            addEventListener: vi.fn(),
            release: vi.fn(),
        });
        vi.stubGlobal('navigator', { wakeLock: { request } });

        primeScreenWake();

        expect(request).toHaveBeenCalledWith('screen');
    });

    it('releases the active wake lock', async () => {
        const release = vi.fn().mockResolvedValue(undefined);
        const request = vi.fn().mockResolvedValue({
            released: false,
            addEventListener: vi.fn(),
            release,
        });
        vi.stubGlobal('navigator', { wakeLock: { request } });

        await requestScreenWake();
        await releaseScreenWake();

        expect(release).toHaveBeenCalled();
    });
});
