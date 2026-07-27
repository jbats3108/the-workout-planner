const REST_NOTIFICATION_TAG = 'ovrload-rest-end';

let sharedAudioContext: AudioContext | null = null;

export type RestAlertOptions = {
    title?: string;
    body?: string;
};

/** @internal test helper */
export function resetRestAlertsForTests(): void {
    if (sharedAudioContext && typeof sharedAudioContext.close === 'function') {
        void sharedAudioContext.close();
    }
    sharedAudioContext = null;
}

export function canUseRestNotifications(): boolean {
    return (
        typeof window !== 'undefined' &&
        window.isSecureContext &&
        'Notification' in window
    );
}

export async function ensureRestNotificationPermission(): Promise<boolean> {
    if (!canUseRestNotifications()) {
        return false;
    }

    if (Notification.permission === 'granted') {
        return true;
    }

    if (Notification.permission === 'denied') {
        return false;
    }

    return (await Notification.requestPermission()) === 'granted';
}

function audioContextCtor(): typeof AudioContext | null {
    if (typeof window === 'undefined') {
        return null;
    }

    return window.AudioContext ?? (window as typeof window & { webkitAudioContext?: typeof AudioContext }).webkitAudioContext ?? null;
}

/** Call from a user gesture (tap) so rest-end audio can play later. */
export function primeRestAlerts(): void {
    const AudioContextCtor = audioContextCtor();
    if (!AudioContextCtor) {
        return;
    }

    if (!sharedAudioContext) {
        sharedAudioContext = new AudioContextCtor();
    }

    if (sharedAudioContext.state === 'suspended') {
        void sharedAudioContext.resume();
    }
}

export function playRestEndSound(): boolean {
    const AudioContextCtor = audioContextCtor();
    if (!AudioContextCtor) {
        return false;
    }

    if (!sharedAudioContext || sharedAudioContext.state !== 'running') {
        return false;
    }

    const context = sharedAudioContext;
    const oscillator = context.createOscillator();
    const gain = context.createGain();

    oscillator.type = 'sine';
    oscillator.frequency.value = 880;
    gain.gain.value = 0.08;

    oscillator.connect(gain);
    gain.connect(context.destination);
    oscillator.start();

    window.setTimeout(() => {
        oscillator.stop();
    }, 180);

    return true;
}

export function vibrateRestEnd(): void {
    if (typeof navigator !== 'undefined' && typeof navigator.vibrate === 'function') {
        navigator.vibrate([180, 90, 180]);
    }
}

export function showRestEndNotification(options: RestAlertOptions = {}): void {
    if (!canUseRestNotifications() || Notification.permission !== 'granted') {
        return;
    }

    const title = options.title ?? 'Rest over';
    const body = options.body ?? 'Time for the next set.';

    try {
        new Notification(title, {
            body,
            tag: REST_NOTIFICATION_TAG,
            silent: false,
        });
    } catch {
        // Safari/iOS may throw when permission state is stale.
    }
}

export function notifyRestEnded(options: RestAlertOptions = {}): void {
    const playedSound = playRestEndSound();
    vibrateRestEnd();

    if (Notification.permission === 'granted' && (!playedSound || document.hidden)) {
        showRestEndNotification(options);
    }
}

/** User-gesture entry point: prime audio and optionally request notification access. */
export function prepareRestAlerts(): void {
    primeRestAlerts();
    void ensureRestNotificationPermission();
}
