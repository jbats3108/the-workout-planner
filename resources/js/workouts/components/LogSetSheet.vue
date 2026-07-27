<script setup lang="ts">
import { cn } from '@/lib/utils';
import { DialogContent, DialogPortal, DialogRoot } from 'reka-ui';
import SheetOverlay from '@/components/ui/sheet/SheetOverlay.vue';
import { onBeforeUnmount, ref, watch } from 'vue';

const open = defineModel<boolean>('open', { required: true });
const keyboardInset = ref(0);

const blockDismiss = (event: Event) => {
    event.preventDefault();
};

const syncKeyboardInset = () => {
    const viewport = window.visualViewport;
    if (!viewport) {
        keyboardInset.value = 0;
        return;
    }

    keyboardInset.value = Math.max(0, window.innerHeight - viewport.height - viewport.offsetTop);
};

watch(open, (isOpen) => {
    const viewport = window.visualViewport;
    if (!viewport) {
        return;
    }

    if (isOpen) {
        syncKeyboardInset();
        viewport.addEventListener('resize', syncKeyboardInset);
        viewport.addEventListener('scroll', syncKeyboardInset);
        return;
    }

    keyboardInset.value = 0;
    viewport.removeEventListener('resize', syncKeyboardInset);
    viewport.removeEventListener('scroll', syncKeyboardInset);
});

onBeforeUnmount(() => {
    window.visualViewport?.removeEventListener('resize', syncKeyboardInset);
    window.visualViewport?.removeEventListener('scroll', syncKeyboardInset);
});
</script>

<template>
    <DialogRoot v-model:open="open">
        <DialogPortal>
            <SheetOverlay />
            <DialogContent
                :class="
                    cn(
                        'bg-background fixed inset-x-0 z-50 flex max-h-[55dvh] flex-col gap-4 rounded-t-2xl border-t p-4 pb-[max(1rem,env(safe-area-inset-bottom))] shadow-lg',
                        'data-[state=open]:animate-in data-[state=closed]:animate-out data-[state=closed]:fade-out-0 data-[state=open]:fade-in-0',
                        'data-[state=closed]:slide-out-to-bottom data-[state=open]:slide-in-from-bottom duration-300',
                    )
                "
                :style="{ bottom: `${keyboardInset}px` }"
                @open-auto-focus.prevent
                @close-auto-focus.prevent
                @pointer-down-outside="blockDismiss"
                @interact-outside="blockDismiss"
                @escape-key-down="blockDismiss"
            >
                <slot />
            </DialogContent>
        </DialogPortal>
    </DialogRoot>
</template>
