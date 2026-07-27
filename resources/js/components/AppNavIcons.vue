<script setup lang="ts">
import { Link, router, usePage } from '@inertiajs/vue3';
import { Dumbbell, LayoutGrid, LogOut, Palette, Settings, Shield, UserRound } from 'lucide-vue-next';
import { computed, type Component } from 'vue';
import { route as ziggyRoute } from 'ziggy-js';

const props = withDefaults(
    defineProps<{
        /** `rail` = icon-only desktop strip; `drawer` = labeled mobile sheet */
        variant?: 'rail' | 'drawer';
    }>(),
    { variant: 'rail' },
);

const emit = defineEmits<{
    navigate: [];
}>();

const page = usePage();
const path = computed(() => page.url.split('?')[0]);
const isAdmin = computed(() => Boolean(page.props.auth.user?.is_admin));
const labeled = computed(() => props.variant === 'drawer');

const route = (name: string, params?: Record<string, unknown>, absolute?: boolean) => ziggyRoute(name, params, absolute, page.props.ziggy);

const isActive = (href: string) => path.value === href || path.value.startsWith(`${href}/`);

const settingsActive = computed(() => path.value.startsWith('/settings/profile') || path.value.startsWith('/settings/appearance'));

const itemClass = (active: boolean) =>
    [
        labeled.value
            ? 'flex w-full items-center gap-3 rounded-md px-3 py-2.5 text-sm font-medium transition-colors'
            : 'flex size-9 items-center justify-center rounded-md transition-colors',
        active ? 'bg-secondary text-primary' : 'text-muted-foreground hover:bg-secondary hover:text-foreground',
    ].join(' ');

const onNavigate = () => emit('navigate');

const logout = () => {
    router.flushAll();
    router.post(route('logout'));
    onNavigate();
};

type NavLink = { href: string; label: string; icon: Component; match: string };

const primaryLinks = computed((): NavLink[] => {
    const links: NavLink[] = [
        { href: route('dashboard'), label: 'Dashboard', icon: LayoutGrid, match: '/dashboard' },
        { href: route('training.edit'), label: 'Training', icon: Dumbbell, match: '/settings/training' },
    ];
    if (isAdmin.value) {
        links.splice(1, 0, { href: route('admin.index'), label: 'Admin', icon: Shield, match: '/admin' });
    }
    return links;
});

const settingsLinks: NavLink[] = [
    { href: route('profile.edit'), label: 'Profile', icon: UserRound, match: '/settings/profile' },
    { href: route('appearance'), label: 'Appearance', icon: Palette, match: '/settings/appearance' },
];
</script>

<template>
    <Link
        :href="route('dashboard')"
        class="mb-6 text-sm font-bold tracking-wide"
        :class="labeled ? 'self-start px-3' : ''"
        aria-label="OVRLOAD home"
        prefetch
        @click="onNavigate"
    >
        <span class="text-primary">OVR</span>
        <span v-if="labeled" class="text-foreground">LOAD</span>
    </Link>

    <nav class="flex flex-1 flex-col gap-1" :class="labeled ? 'items-stretch' : 'items-center'">
        <Link
            v-for="link in primaryLinks"
            :key="link.match"
            :href="link.href"
            :class="itemClass(isActive(link.match))"
            :aria-label="link.label"
            prefetch
            @click="onNavigate"
        >
            <component :is="link.icon" class="size-4 shrink-0" />
            <span v-if="labeled">{{ link.label }}</span>
        </Link>
    </nav>

    <div class="flex flex-col gap-1" :class="labeled ? 'items-stretch' : 'items-center'">
        <template v-if="labeled">
            <p class="mb-1 px-3 text-xs tracking-[0.15em] text-muted-foreground uppercase">Settings</p>
            <Link
                v-for="link in settingsLinks"
                :key="link.match"
                :href="link.href"
                :class="itemClass(isActive(link.match))"
                :aria-label="link.label"
                prefetch
                @click="onNavigate"
            >
                <component :is="link.icon" class="size-4 shrink-0" />
                <span>{{ link.label }}</span>
            </Link>
        </template>

        <Link v-else :href="route('profile.edit')" :class="itemClass(settingsActive)" aria-label="Settings" prefetch @click="onNavigate">
            <Settings class="size-4" />
        </Link>

        <button type="button" :class="[itemClass(false), labeled ? 'justify-start' : '']" aria-label="Log out" @click="logout">
            <LogOut class="size-4 shrink-0" />
            <span v-if="labeled">Log out</span>
        </button>
    </div>
</template>
