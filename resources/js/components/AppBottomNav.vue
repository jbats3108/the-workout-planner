<script setup lang="ts">
import { useZiggyRoute } from '@/shared/composables/useZiggyRoute';
import { isPathActive, isSettingsActive, primaryNavItems } from '@/shared/lib/appNav';
import { Link, usePage } from '@inertiajs/vue3';
import { Dumbbell, History, LayoutGrid, Settings } from 'lucide-vue-next';
import { computed, type Component } from 'vue';

const page = usePage();
const route = useZiggyRoute();
const path = computed(() => page.url.split('?')[0]);

const tabIcons: Record<string, Component> = {
    '/dashboard': LayoutGrid,
    '/history': History,
    '/settings/training': Dumbbell,
};

type Tab = { href: string; label: string; icon: Component; active: boolean };

const tabs = computed((): Tab[] => [
    ...primaryNavItems(route, { isAdmin: false }).map((link) => ({
        href: link.href,
        label: link.label,
        icon: tabIcons[link.match],
        active: isPathActive(path.value, link.match),
    })),
    {
        href: route('profile.edit'),
        label: 'Settings',
        icon: Settings,
        active: isSettingsActive(path.value),
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
