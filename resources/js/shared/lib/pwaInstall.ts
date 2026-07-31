export const PWA_INSTALL_DISMISS_KEY = 'ovrload:pwa-install-dismissed';

export function isStandaloneDisplayMode(): boolean {
    if (typeof window === 'undefined') {
        return false;
    }

    if (window.matchMedia('(display-mode: standalone)').matches) {
        return true;
    }

    const navigatorWithStandalone = window.navigator as Navigator & { standalone?: boolean };

    return navigatorWithStandalone.standalone === true;
}

export function isIosSafari(): boolean {
    if (typeof window === 'undefined') {
        return false;
    }

    const { userAgent, platform, maxTouchPoints } = window.navigator;
    const isIos = /iPad|iPhone|iPod/.test(userAgent) || (platform === 'MacIntel' && maxTouchPoints > 1);
    const isSafari = /Safari/.test(userAgent) && !/CriOS|FxiOS|EdgiOS/.test(userAgent);

    return isIos && isSafari;
}

export function shouldShowPwaInstallPrompt(): boolean {
    if (typeof window === 'undefined') {
        return false;
    }

    if (isStandaloneDisplayMode()) {
        return false;
    }

    if (localStorage.getItem(PWA_INSTALL_DISMISS_KEY)) {
        return false;
    }

    return isIosSafari();
}

export function dismissPwaInstallPrompt(): void {
    localStorage.setItem(PWA_INSTALL_DISMISS_KEY, '1');
}
