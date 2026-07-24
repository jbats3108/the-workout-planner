<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Routine } from '@/types/workouts';
import { Head, Link, useForm } from '@inertiajs/vue3';

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

const createForm = useForm({
    name: '',
});

const createRoutine = () => {
    createForm.post(route('routines.create'), {
        onSuccess: () => createForm.reset('name'),
    });
};
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl bg-zinc-950 p-4 text-zinc-100">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h2 class="text-xl font-semibold">My Routines</h2>
                <form class="flex gap-2" @submit.prevent="createRoutine">
                    <input
                        v-model="createForm.name"
                        class="rounded border border-zinc-700 bg-zinc-900 px-3 py-2 text-sm"
                        placeholder="New routine name"
                        required
                    />
                    <button type="submit" class="rounded-full bg-lime-400 px-4 py-2 text-sm font-semibold text-zinc-950" :disabled="createForm.processing">
                        Create
                    </button>
                </form>
            </div>

            <div class="grid auto-rows-min gap-3 md:grid-cols-3">
                <Link
                    v-for="routine in props.data.routines"
                    :key="routine.id"
                    :href="route('routines.edit', routine.id)"
                    class="rounded-xl border border-zinc-800 bg-zinc-900 p-4 transition hover:border-lime-400/60"
                >
                    <h3 class="text-lg font-semibold">{{ routine.name }}</h3>
                    <p class="mt-1 font-mono text-xs text-zinc-500">
                        Deload {{ routine.deload_weight_factor }}w / {{ routine.deload_reps_factor }}r
                    </p>
                </Link>
            </div>
            <p v-if="!props.data.routines.length" class="text-sm text-zinc-500">No routines yet. Create one above.</p>
        </div>
    </AppLayout>
</template>
