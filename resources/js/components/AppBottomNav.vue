<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { Dumbbell, History, LayoutGrid, Settings } from 'lucide-vue-next';
import { computed, type Component } from 'vue';
import { route as ziggyRoute } from 'ziggy-js';

const page = usePage();
const path = computed(() => page.url.split('?')[0]);

const route = (name: string, params?: Record<string, unknown>, absolute?: boolean) => ziggyRoute(name, params, absolute, page.props.ziggy);

const isActive = (match: string) => path.value === match || path.value.startsWith(`${match}/`);

const settingsActive = computed(() => path.value.startsWith('/settings/profile') || path.value.startsWith('/settings/appearance'));

type Tab = { href: string; label: string; icon: Component; active: boolean };

const tabs = computed((): Tab[] => [
    {
        href: route('dashboard'),
        label: 'Dashboard',
        icon: LayoutGrid,
        active: isActive('/dashboard'),
    },
    {
        href: route('history.index'),
        label: 'History',
        icon: History,
        active: isActive('/history'),
    },
    {
        href: route('training.edit'),
        label: 'Training',
        icon: Dumbbell,
        active: isActive('/settings/training'),
    },
    {
        href: route('profile.edit'),
        label: 'Settings',
        icon: Settings,
        active: settingsActive.value,
    },
]);
</script>

<template>
    <nav class="fixed inset-x-0 bottom-0 z-40 border-t border-border bg-card pb-[env(safe-area-inset-bottom,0px)] md:hidden" aria-label="Primary">
        <ul class="grid h-14 grid-cols-4">
            <li v-for="tab in tabs" :key="tab.label">
                <Link
                    :href="tab.href"
                    class="flex h-full flex-col items-center justify-center gap-0.5 text-[10px] font-medium"
                    :class="tab.active ? 'text-primary' : 'text-muted-foreground'"
                    :aria-current="tab.active ? 'page' : undefined"
                    prefetch
                >
                    <component :is="tab.icon" class="size-5" aria-hidden="true" />
                    <span>{{ tab.label }}</span>
                </Link>
            </li>
        </ul>
    </nav>
</template>
