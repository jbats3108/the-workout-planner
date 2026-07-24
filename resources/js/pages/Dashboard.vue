<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Routine } from '@/types/workouts';
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

type InProgressWorkout = {
    id: number;
    routine_name: string;
    mode: string;
};

type DashboardRoutine = Routine & {
    can_start?: boolean;
};

const props = defineProps<{
    data: {
        routines: DashboardRoutine[];
        in_progress_workout: InProgressWorkout | null;
    };
}>();

const page = usePage();
const formErrors = computed(() => Object.values(page.props.errors ?? {}));

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

const startWorkout = (routineId: number, mode: 'normal' | 'deload' = 'normal') => {
    router.post(route('workouts.store', { routine: routineId }), { mode });
};

const canStart = (routine: DashboardRoutine) => !props.data.in_progress_workout && routine.can_start === true;
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl bg-zinc-950 p-4 text-zinc-100">
            <div
                v-if="formErrors.length"
                class="rounded-xl border border-red-500/40 bg-red-950/40 px-4 py-3 text-sm text-red-200"
            >
                <p v-for="(error, index) in formErrors" :key="index">{{ error }}</p>
            </div>

            <div
                v-if="data.in_progress_workout"
                class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-lime-400/40 bg-zinc-900 px-4 py-3"
            >
                <div>
                    <p class="text-xs uppercase tracking-wide text-lime-400">In progress</p>
                    <p class="text-lg font-semibold">{{ data.in_progress_workout.routine_name }}</p>
                    <p class="font-mono text-xs text-zinc-500">{{ data.in_progress_workout.mode }}</p>
                </div>
                <Link
                    :href="route('workouts.play', data.in_progress_workout.id)"
                    class="rounded-full bg-lime-400 px-5 py-2 text-sm font-semibold text-zinc-950"
                >
                    Resume
                </Link>
            </div>

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
                <div
                    v-for="routine in props.data.routines"
                    :key="routine.id"
                    class="rounded-xl border border-zinc-800 bg-zinc-900 p-4"
                >
                    <Link :href="route('routines.edit', routine.id)" class="block transition hover:text-lime-400">
                        <h3 class="text-lg font-semibold">{{ routine.name }}</h3>
                        <p class="mt-1 font-mono text-xs text-zinc-500">
                            Deload {{ routine.deload_weight_factor }}w / {{ routine.deload_reps_factor }}r
                        </p>
                    </Link>
                    <p v-if="!routine.can_start" class="mt-3 text-xs text-zinc-500">
                        Add exercises in the editor before starting.
                    </p>
                    <div class="mt-4 flex flex-wrap gap-2">
                        <button
                            type="button"
                            class="rounded-full bg-lime-400 px-4 py-2 text-xs font-semibold text-zinc-950 disabled:opacity-40"
                            :disabled="!canStart(routine)"
                            :title="!routine.can_start ? 'Add exercises first' : data.in_progress_workout ? 'Finish or resume the current workout' : 'Start workout'"
                            @click="startWorkout(routine.id, 'normal')"
                        >
                            Start
                        </button>
                        <button
                            type="button"
                            class="rounded-full border border-zinc-600 px-4 py-2 text-xs text-zinc-200 disabled:opacity-40"
                            :disabled="!canStart(routine)"
                            :title="!routine.can_start ? 'Add exercises first' : data.in_progress_workout ? 'Finish or resume the current workout' : 'Start deload'"
                            @click="startWorkout(routine.id, 'deload')"
                        >
                            Deload
                        </button>
                    </div>
                </div>
            </div>
            <p v-if="!props.data.routines.length" class="text-sm text-zinc-500">No routines yet. Create one above.</p>
        </div>
    </AppLayout>
</template>
