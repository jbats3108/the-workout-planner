import { dismissPwaInstallPrompt, isStandaloneDisplayMode, shouldShowPwaInstallPrompt } from '@/shared/lib/pwaInstall';
import { onMounted, ref } from 'vue';

export function usePwaInstall() {
    const visible = ref(false);
    const standalone = ref(false);

    onMounted(() => {
        standalone.value = isStandaloneDisplayMode();
        visible.value = shouldShowPwaInstallPrompt();
    });

    function dismiss(): void {
        dismissPwaInstallPrompt();
        visible.value = false;
    }

    return {
        visible,
        standalone,
        dismiss,
    };
}
