<script setup lang="ts">
import SheetOverlay from '@/components/ui/sheet/SheetOverlay.vue';
import { cn } from '@/lib/utils';
import { DialogContent, DialogPortal, DialogRoot } from 'reka-ui';

const open = defineModel<boolean>('open', { required: true });

const blockDismiss = (event: Event) => {
    event.preventDefault();
};
</script>

<template>
    <DialogRoot v-model:open="open">
        <DialogPortal>
            <SheetOverlay />
            <DialogContent
                :class="
                    cn(
                        // Mobile: full-bleed stage (press-when-done log)
                        'fixed inset-0 z-50 flex flex-col gap-4 overflow-hidden bg-background px-4 pt-[max(1rem,env(safe-area-inset-top))] pb-[max(1rem,env(safe-area-inset-bottom))] shadow-lg',
                        'data-[state=closed]:animate-out data-[state=closed]:fade-out-0 data-[state=open]:animate-in data-[state=open]:fade-in-0',
                        'duration-300 data-[state=closed]:slide-out-to-bottom data-[state=open]:slide-in-from-bottom',
                        // Desktop: centered dialog over dimmed player
                        'md:inset-auto md:top-1/2 md:left-1/2 md:h-auto md:max-h-[min(40rem,calc(100dvh-4rem))] md:w-full md:max-w-md md:-translate-x-1/2 md:-translate-y-1/2',
                        'md:rounded-xl md:border md:border-border md:p-6 md:shadow-2xl',
                        'md:data-[state=closed]:slide-out-to-bottom-0 md:data-[state=open]:slide-in-from-bottom-0',
                        'md:data-[state=closed]:zoom-out-95 md:data-[state=open]:zoom-in-95',
                    )
                "
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
