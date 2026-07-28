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
                        'fixed inset-0 z-50 flex flex-col gap-4 overflow-hidden bg-background px-4 pt-[max(1rem,env(safe-area-inset-top))] pb-[max(1rem,env(safe-area-inset-bottom))] shadow-lg',
                        'data-[state=closed]:animate-out data-[state=closed]:fade-out-0 data-[state=open]:animate-in data-[state=open]:fade-in-0',
                        'duration-300 data-[state=closed]:slide-out-to-bottom data-[state=open]:slide-in-from-bottom',
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
