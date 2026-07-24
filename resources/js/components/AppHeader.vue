<script setup lang="ts">
import AppNavIcons from '@/components/AppNavIcons.vue';
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import { Sheet, SheetContent, SheetDescription, SheetTitle, SheetTrigger } from '@/components/ui/sheet';
import type { BreadcrumbItem } from '@/types';
import { ref } from 'vue';

interface Props {
    breadcrumbs?: BreadcrumbItem[];
}

const props = withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});

const mobileOpen = ref(false);
</script>

<template>
    <header class="flex h-12 shrink-0 items-center gap-3 border-b border-border px-3 text-muted-foreground md:px-4">
        <Sheet v-model:open="mobileOpen">
            <SheetTrigger as-child>
                <button
                    type="button"
                    class="flex size-9 items-center justify-center rounded-md border border-border bg-secondary/40 text-xs font-bold tracking-wide md:hidden"
                    aria-label="Open menu"
                >
                    <span class="text-primary">OVR</span>
                </button>
            </SheetTrigger>

            <SheetContent
                side="left"
                class="w-[min(20rem,85vw)] border-border p-0 sm:max-w-sm [&>button]:right-3 [&>button]:top-3"
            >
                <SheetTitle class="sr-only">Navigation</SheetTitle>
                <SheetDescription class="sr-only">Main app navigation</SheetDescription>
                <div class="flex h-full flex-col px-2 py-4 pt-12">
                    <AppNavIcons variant="drawer" @navigate="mobileOpen = false" />
                </div>
            </SheetContent>
        </Sheet>

        <Breadcrumbs v-if="props.breadcrumbs.length > 0" :breadcrumbs="breadcrumbs" />
    </header>
</template>
