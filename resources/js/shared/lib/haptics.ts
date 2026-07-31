/** Short device vibration helpers (no-op when Vibration API is missing). */

export const HAPTIC_TAP_MS = 24;
export const HAPTIC_CONFIRM_MS = 40;

function vibrate(pattern: number | number[]): void {
    if (typeof navigator !== 'undefined' && typeof navigator.vibrate === 'function') {
        navigator.vibrate(pattern);
    }
}

/** Light pulse — e.g. opening the log sheet (Done). */
export function hapticTap(): void {
    vibrate(HAPTIC_TAP_MS);
}

/** Slightly longer pulse — e.g. confirming Log set. */
export function hapticConfirm(): void {
    vibrate(HAPTIC_CONFIRM_MS);
}
