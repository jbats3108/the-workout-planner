<script setup lang="ts">
import { Link, router, usePage } from '@inertiajs/vue3';
import { LayoutGrid, LogOut, Settings } from 'lucide-vue-next';
import { computed } from 'vue';

const emit = defineEmits<{
    navigate: [];
}>();

const page = usePage();
const path = computed(() => page.url.split('?')[0]);

const isActive = (href: string) => path.value === href || path.value.startsWith(`${href}/`);

const iconClass = (active: boolean) =>
    active
        ? 'bg-secondary text-primary'
        : 'text-muted-foreground hover:bg-secondary hover:text-foreground';

const onNavigate = () => emit('navigate');

const logout = () => {
    router.flushAll();
    router.post(route('logout'));
    onNavigate();
};
</script>

<template>
    <Link
        :href="route('dashboard')"
        class="mb-6 text-sm font-bold tracking-wide"
        aria-label="OVRLOAD home"
        @click="onNavigate"
    >
        <span class="text-primary">OVR</span>
    </Link>

    <nav class="flex flex-1 flex-col items-center gap-1">
        <Link
            :href="route('dashboard')"
            class="flex size-9 items-center justify-center rounded-md transition-colors"
            :class="iconClass(isActive('/dashboard'))"
            aria-label="Dashboard"
            @click="onNavigate"
        >
            <LayoutGrid class="size-4" />
        </Link>
    </nav>

    <div class="flex flex-col items-center gap-1">
        <Link
            :href="route('profile.edit')"
            class="flex size-9 items-center justify-center rounded-md transition-colors"
            :class="iconClass(isActive('/settings'))"
            aria-label="Settings"
            prefetch
            @click="onNavigate"
        >
            <Settings class="size-4" />
        </Link>

        <button
            type="button"
            class="flex size-9 items-center justify-center rounded-md text-muted-foreground transition-colors hover:bg-secondary hover:text-foreground"
            aria-label="Log out"
            @click="logout"
        >
            <LogOut class="size-4" />
        </button>
    </div>
</template>
