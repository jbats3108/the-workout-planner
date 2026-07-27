import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import {
    canUseRestNotifications,
    ensureRestNotificationPermission,
    notifyRestEnded,
    playRestEndSound,
    prepareRestAlerts,
    primeRestAlerts,
    resetRestAlertsForTests,
    showRestEndNotification,
    vibrateRestEnd,
} from '@/workouts/lib/restAlert';

function mockNotification(permission: NotificationPermission): void {
    const requestPermission = vi.fn().mockResolvedValue('granted' as NotificationPermission);
    const NotificationCtor = vi.fn(function MockNotification(
        this: { title: string; options?: NotificationOptions },
        title: string,
        options?: NotificationOptions,
    ) {
        this.title = title;
        this.options = options;
    }) as unknown as typeof Notification;

    NotificationCtor.permission = permission;
    NotificationCtor.requestPermission = requestPermission;

    vi.stubGlobal('Notification', NotificationCtor);
}

describe('restAlert', () => {
    beforeEach(() => {
        resetRestAlertsForTests();
        mockNotification('granted');
        vi.stubGlobal('navigator', {
            vibrate: vi.fn(),
        });
        Object.defineProperty(window, 'isSecureContext', {
            configurable: true,
            value: true,
        });
        Object.defineProperty(document, 'hidden', {
            configurable: true,
            value: false,
        });
    });

    afterEach(() => {
        resetRestAlertsForTests();
        vi.unstubAllGlobals();
        vi.useRealTimers();
    });

    it('detects notification support', () => {
        expect(canUseRestNotifications()).toBe(true);
    });

    it('requests notification permission when default', async () => {
        mockNotification('default');

        await expect(ensureRestNotificationPermission()).resolves.toBe(true);
        expect(Notification.requestPermission).toHaveBeenCalled();
    });

    it('skips permission request when denied', async () => {
        mockNotification('denied');

        await expect(ensureRestNotificationPermission()).resolves.toBe(false);
        expect(Notification.requestPermission).not.toHaveBeenCalled();
    });

    it('vibrates on rest end', () => {
        vibrateRestEnd();
        expect(navigator.vibrate).toHaveBeenCalledWith([180, 90, 180]);
    });

    it('shows a notification when permission is granted', () => {
        showRestEndNotification({ title: 'Done resting', body: 'Next set' });
        expect(Notification).toHaveBeenCalledWith(
            'Done resting',
            expect.objectContaining({
                body: 'Next set',
                tag: 'ovrload-rest-end',
            }),
        );
    });

    it('plays sound and vibrates in the foreground', () => {
        const start = vi.fn();
        const stop = vi.fn();
        const connect = vi.fn();
        const resume = vi.fn().mockResolvedValue(undefined);

        vi.stubGlobal(
            'AudioContext',
            vi.fn(() => ({
                state: 'running',
                createOscillator: vi.fn(() => ({
                    type: 'sine',
                    frequency: { value: 0 },
                    connect,
                    start,
                    stop,
                })),
                createGain: vi.fn(() => ({
                    gain: { value: 0 },
                    connect,
                })),
                destination: {},
                resume,
            })),
        );

        primeRestAlerts();
        notifyRestEnded();

        expect(start).toHaveBeenCalled();
        expect(navigator.vibrate).toHaveBeenCalled();
        expect(Notification).not.toHaveBeenCalled();
    });

    it('shows a notification when the page is hidden', () => {
        Object.defineProperty(document, 'hidden', {
            configurable: true,
            value: true,
        });

        notifyRestEnded();

        expect(Notification).toHaveBeenCalled();
    });

    it('creates an audio context for the rest-end tone', () => {
        resetRestAlertsForTests();
        const start = vi.fn();
        const stop = vi.fn();
        const connect = vi.fn();
        const resume = vi.fn().mockResolvedValue(undefined);
        const createOscillator = vi.fn(() => ({
            type: 'sine',
            frequency: { value: 0 },
            connect,
            start,
            stop,
        }));

        vi.stubGlobal(
            'AudioContext',
            vi.fn(() => ({
                state: 'running',
                createOscillator,
                createGain: vi.fn(() => ({
                    gain: { value: 0 },
                    connect,
                })),
                destination: {},
                resume,
            })),
        );
        vi.useFakeTimers();

        primeRestAlerts();
        playRestEndSound();
        vi.runAllTimers();

        expect(createOscillator).toHaveBeenCalled();
        expect(start).toHaveBeenCalled();
        expect(stop).toHaveBeenCalled();
    });

    it('falls back to notification when audio was not primed', () => {
        resetRestAlertsForTests();
        notifyRestEnded();
        expect(Notification).toHaveBeenCalled();
    });

    it('primes audio and requests permission from a user gesture helper', () => {
        resetRestAlertsForTests();
        mockNotification('default');
        const resume = vi.fn().mockResolvedValue(undefined);
        vi.stubGlobal(
            'AudioContext',
            vi.fn(() => ({
                state: 'suspended',
                createOscillator: vi.fn(),
                createGain: vi.fn(),
                destination: {},
                resume,
            })),
        );

        prepareRestAlerts();

        expect(resume).toHaveBeenCalled();
        expect(Notification.requestPermission).toHaveBeenCalled();
    });
});
