<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { BreadcrumbItem } from '@/types';
import { RoutineType } from '@/types/workouts';
import { Head } from '@inertiajs/vue3';

defineProps<{
    routine_types: RoutineType[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];

</script>

<template>

    <Head title="Create a Routine" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <h1 class="text-xl border-2 p-2">Create a new Routine</h1>

    </AppLayout>
</template>