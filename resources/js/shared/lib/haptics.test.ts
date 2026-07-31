import { HAPTIC_CONFIRM_MS, HAPTIC_TAP_MS, hapticConfirm, hapticTap } from '@/shared/lib/haptics';
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';

describe('haptics', () => {
    const vibrate = vi.fn();

    beforeEach(() => {
        vibrate.mockReset();
        Object.defineProperty(navigator, 'vibrate', {
            configurable: true,
            value: vibrate,
        });
    });

    afterEach(() => {
        // leave navigator alone for other suites
    });

    it('fires a short pulse on tap', () => {
        hapticTap();
        expect(vibrate).toHaveBeenCalledWith(HAPTIC_TAP_MS);
    });

    it('fires a confirm pulse', () => {
        hapticConfirm();
        expect(vibrate).toHaveBeenCalledWith(HAPTIC_CONFIRM_MS);
    });

    it('no-ops when vibrate is unavailable', () => {
        Object.defineProperty(navigator, 'vibrate', {
            configurable: true,
            value: undefined,
        });
        expect(() => hapticTap()).not.toThrow();
        expect(() => hapticConfirm()).not.toThrow();
    });
});
