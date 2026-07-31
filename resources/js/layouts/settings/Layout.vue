<script setup lang="ts">
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { Separator } from '@/components/ui/separator';
import { type NavItem } from '@/types';
import { Link, router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { route as ziggyRoute } from 'ziggy-js';

const page = usePage();
const route = (name: string, params?: Record<string, unknown>, absolute?: boolean) => ziggyRoute(name, params, absolute, page.props.ziggy);
const isAdmin = computed(() => Boolean(page.props.auth.user?.is_admin));

const sidebarNavItems = computed((): NavItem[] => {
    const items: NavItem[] = [
        {
            title: 'Profile',
            href: route('profile.edit'),
        },
        {
            title: 'Appearance',
            href: route('appearance'),
        },
    ];
    if (isAdmin.value) {
        items.push({
            title: 'Admin',
            href: route('admin.index'),
        });
    }
    return items;
});

const currentPath = computed(() => page.url.split('?')[0]);

const logout = () => {
    router.flushAll();
    router.post(route('logout'));
};
</script>

<template>
    <div class="px-4 py-6">
        <Heading title="Settings" description="Manage your profile and account settings" />

        <div class="flex flex-col lg:flex-row lg:space-x-12">
            <aside class="w-full max-w-xl lg:w-48">
                <nav class="flex flex-col gap-1">
                    <Button
                        v-for="item in sidebarNavItems"
                        :key="item.href"
                        variant="ghost"
                        :class="['w-full justify-start', { 'bg-muted': currentPath === item.href }]"
                        as-child
                    >
                        <Link :href="item.href">
                            {{ item.title }}
                        </Link>
                    </Button>
                    <Button type="button" variant="ghost" class="w-full justify-start text-muted-foreground" @click="logout"> Log out </Button>
                </nav>
            </aside>

            <Separator class="my-6 lg:hidden" />

            <div class="flex-1 md:max-w-2xl">
                <section class="max-w-xl space-y-12">
                    <slot />
                </section>
            </div>
        </div>
    </div>
</template>
