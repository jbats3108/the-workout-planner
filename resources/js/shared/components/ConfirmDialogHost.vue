<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { confirmState, settleConfirm } from '@/shared/lib/confirmDialog';

const onOpenChange = (open: boolean) => {
    if (!open) {
        settleConfirm(false);
    }
};
</script>

<template>
    <Dialog :open="confirmState.open" @update:open="onOpenChange">
        <DialogContent class="sm:max-w-md" @open-auto-focus.prevent>
            <DialogHeader class="space-y-2 text-left">
                <DialogTitle>{{ confirmState.title }}</DialogTitle>
                <DialogDescription v-if="confirmState.description">
                    {{ confirmState.description }}
                </DialogDescription>
            </DialogHeader>
            <DialogFooter class="gap-2 sm:justify-end">
                <Button type="button" variant="secondary" @click="settleConfirm(false)">
                    {{ confirmState.cancelLabel }}
                </Button>
                <Button type="button" :variant="confirmState.variant === 'destructive' ? 'destructive' : 'default'" @click="settleConfirm(true)">
                    {{ confirmState.confirmLabel }}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
