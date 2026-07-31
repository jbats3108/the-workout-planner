<script setup lang="ts">
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import type { BreadcrumbItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { route as ziggyRoute } from 'ziggy-js';

interface Props {
    breadcrumbs?: BreadcrumbItem[];
}

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});

const page = usePage();
const route = (name: string, params?: Record<string, unknown>, absolute?: boolean) => ziggyRoute(name, params, absolute, page.props.ziggy);
</script>

<template>
    <header class="flex h-14 shrink-0 items-center gap-3 border-b border-border px-3 text-muted-foreground md:h-12 md:px-4">
        <Link :href="route('dashboard')" class="text-sm font-bold tracking-wide md:hidden" aria-label="OVRLOAD home" prefetch>
            <span class="text-primary">OVR</span>
        </Link>

        <Breadcrumbs v-if="breadcrumbs.length > 0" :breadcrumbs="breadcrumbs" />
    </header>
</template>
