<script setup lang="ts">
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import { useZiggyRoute } from '@/shared/composables/useZiggyRoute';
import type { BreadcrumbItem } from '@/types';
import { Link } from '@inertiajs/vue3';

interface Props {
    breadcrumbs?: BreadcrumbItem[];
}

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});

const route = useZiggyRoute();
</script>

<template>
    <header class="flex h-14 shrink-0 items-center gap-3 border-b border-border px-3 text-muted-foreground md:h-12 md:px-4">
        <Link :href="route('dashboard')" class="text-sm font-bold tracking-wide md:hidden" aria-label="OVRLOAD home" prefetch>
            <span class="text-primary">OVR</span>
        </Link>

        <Breadcrumbs v-if="breadcrumbs.length > 0" :breadcrumbs="breadcrumbs" />
    </header>
</template>
