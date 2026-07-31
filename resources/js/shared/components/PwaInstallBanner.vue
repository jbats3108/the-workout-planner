<script setup lang="ts">
import { usePwaInstall } from '@/shared/composables/usePwaInstall';
import { Share, X } from 'lucide-vue-next';

const { visible, dismiss } = usePwaInstall();
</script>

<template>
    <div
        v-if="visible"
        class="fixed inset-x-0 bottom-0 z-50 border-t border-border bg-card px-4 pt-3 pb-[max(0.75rem,env(safe-area-inset-bottom,0px))] shadow-lg md:hidden"
        role="region"
        aria-label="Install OVRLOAD"
    >
        <div class="mx-auto flex max-w-lg items-start gap-3">
            <div class="min-w-0 flex-1 text-sm text-foreground">
                <p class="font-medium">Install OVRLOAD</p>
                <p class="mt-1 text-muted-foreground">
                    Tap
                    <Share class="mx-0.5 inline size-4 align-text-bottom" aria-hidden="true" />
                    Share, then <span class="font-medium text-foreground">Add to Home Screen</span> for full-screen workouts.
                </p>
            </div>
            <button
                type="button"
                class="flex size-9 shrink-0 items-center justify-center rounded-md border border-border text-muted-foreground hover:bg-secondary/60 hover:text-foreground"
                aria-label="Dismiss install instructions"
                @click="dismiss"
            >
                <X class="size-4" />
            </button>
        </div>
    </div>
</template>
