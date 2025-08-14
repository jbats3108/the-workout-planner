<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Routine } from '@/types/workouts';
import { Head } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';

const props = defineProps<{
    data: {
        routines: Routine[];
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];
</script>

<template>

    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <h2 class="text-xl border-2 p-2">My Routines</h2>

            <Link href="/create-routine"
                class="mt-8 rounded-md bg-primary p-3 transition-opacity hover:opacity-90 w-1/3">
            Create New
            </Link>

            <div class="grid auto-rows-min gap-4 md:grid-cols-3">

                <div v-for="(routine, key) in props.data.routines" :key="key" class="border-2 p-3">
                    <h3 class="text-lg font-semibold">{{ routine.name }}</h3>
                    <p class="text-sm color: var(--color-accent)">Type: {{ routine.routine_type }}</p>
                </div>

            </div>
        </div>
    </AppLayout>
</template>
