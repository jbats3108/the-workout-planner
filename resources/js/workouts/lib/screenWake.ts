let activeWakeLock: WakeLockSentinel | null = null;

export function canUseScreenWake(): boolean {
    return typeof navigator !== 'undefined' && window.isSecureContext && 'wakeLock' in navigator;
}

export async function requestScreenWake(): Promise<boolean> {
    if (!canUseScreenWake() || document.visibilityState !== 'visible') {
        return false;
    }

    if (activeWakeLock && !activeWakeLock.released) {
        return true;
    }

    try {
        activeWakeLock = await navigator.wakeLock.request('screen');
        activeWakeLock.addEventListener('release', () => {
            activeWakeLock = null;
        });
        return true;
    } catch {
        return false;
    }
}

export async function releaseScreenWake(): Promise<void> {
    try {
        await activeWakeLock?.release();
    } catch {
        // already released
    }
    activeWakeLock = null;
}

/** Call from a user gesture so wake lock is more likely to stick. */
export function primeScreenWake(): void {
    void requestScreenWake();
}

/** @internal test helper */
export function resetScreenWakeForTests(): void {
    activeWakeLock = null;
}
