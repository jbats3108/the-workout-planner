<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import type { Routine } from '@/routines/types';
import { type BreadcrumbItem } from '@/types';
import type { HistoryWorkout, InProgressWorkout } from '@/workouts/types';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { Pencil, Trash2 } from 'lucide-vue-next';
import { computed } from 'vue';

const props = defineProps<{
    data: {
        routines: Routine[];
        in_progress_workout: InProgressWorkout | null;
        recent_finished_workouts: HistoryWorkout[];
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

const startWorkout = (routineId: number, mode: 'normal' | 'deload' = 'normal') => {
    router.post(route('workouts.store', { routine: routineId }), { mode });
};

const canStart = (routine: Routine) => !props.data.in_progress_workout && routine.can_start === true;

const finishInProgress = () => {
    const workout = props.data.in_progress_workout;
    if (!workout) return;
    if (!confirm('Finish this workout now? Incomplete sets stay incomplete.')) return;
    router.post(route('workouts.finish', workout.id));
};

const abandonInProgress = () => {
    const workout = props.data.in_progress_workout;
    if (!workout) return;
    if (!confirm('Abandon this workout? Logged sets are kept but it will not count as finished.')) return;
    router.post(route('workouts.discard', workout.id));
};

const deleteRoutine = (routine: Routine) => {
    if (!confirm(`Delete “${routine.name}”? It will be archived and removed from your list.`)) {
        return;
    }
    router.delete(route('routines.delete', routine.id));
};

const formatFinishedAt = (iso: string) => {
    if (!iso) return '';
    return new Date(iso).toLocaleDateString(undefined, { dateStyle: 'medium' });
};
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl bg-background p-4 text-foreground">
            <div v-if="formErrors.length" class="rounded-xl border border-destructive/40 bg-destructive/10 px-4 py-3 text-sm text-destructive">
                <p v-for="(error, index) in formErrors" :key="index">{{ error }}</p>
            </div>

            <div
                v-if="data.in_progress_workout"
                class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-primary/40 bg-card px-4 py-3"
            >
                <div>
                    <p class="text-xs tracking-wide text-primary uppercase">In progress</p>
                    <p class="text-lg font-semibold">{{ data.in_progress_workout.routine_name }}</p>
                    <p class="font-mono text-xs text-muted-foreground">{{ data.in_progress_workout.mode }}</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <Link
                        :href="route('workouts.play', data.in_progress_workout.id)"
                        class="rounded-full bg-primary px-5 py-2 text-sm font-semibold text-primary-foreground"
                    >
                        Resume
                    </Link>
                    <button type="button" class="rounded-full border border-border px-4 py-2 text-sm text-foreground" @click="finishInProgress">
                        Finish
                    </button>
                    <button
                        type="button"
                        class="rounded-full border border-destructive/40 px-4 py-2 text-sm text-destructive"
                        @click="abandonInProgress"
                    >
                        Abandon
                    </button>
                </div>
            </div>

            <div v-if="data.recent_finished_workouts.length" class="space-y-3">
                <div class="flex items-center justify-between gap-3">
                    <h2 class="text-xl font-semibold">Recent</h2>
                    <Link :href="route('history.index')" class="text-sm text-primary hover:underline">All history</Link>
                </div>
                <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                    <Link
                        v-for="workout in data.recent_finished_workouts"
                        :key="workout.id"
                        :href="route('history.show', workout.id)"
                        class="rounded-xl border border-border bg-card px-4 py-3 transition-colors hover:border-primary/40"
                    >
                        <p class="font-medium">{{ workout.routine_name }}</p>
                        <p class="mt-1 font-mono text-xs text-muted-foreground">{{ workout.mode }} · {{ formatFinishedAt(workout.finished_at) }}</p>
                    </Link>
                </div>
            </div>

            <div class="flex flex-wrap items-center justify-between gap-3">
                <h2 class="text-xl font-semibold">My Routines</h2>
                <Link :href="route('routines.create')" class="rounded-full bg-primary px-4 py-2 text-sm font-semibold text-primary-foreground">
                    Create
                </Link>
            </div>

            <div class="grid auto-rows-min gap-3 md:grid-cols-3">
                <div v-for="routine in props.data.routines" :key="routine.id" class="rounded-xl border border-border bg-card p-4">
                    <div>
                        <h3 class="text-lg font-semibold">{{ routine.name }}</h3>
                        <p class="mt-1 font-mono text-xs text-muted-foreground">
                            Deload {{ routine.deload_weight_factor }}w / {{ routine.deload_reps_factor }}r
                        </p>
                    </div>
                    <p v-if="!routine.can_start" class="mt-3 text-xs text-muted-foreground">Add exercises in the editor before starting.</p>
                    <div class="mt-4 flex flex-wrap items-center justify-between gap-2">
                        <div class="flex flex-wrap items-center gap-2">
                            <button
                                type="button"
                                class="rounded-full bg-primary px-5 py-2.5 text-sm font-semibold text-primary-foreground disabled:opacity-40"
                                :disabled="!canStart(routine)"
                                :title="
                                    !routine.can_start
                                        ? 'Add exercises first'
                                        : data.in_progress_workout
                                          ? 'Finish or resume the current workout'
                                          : 'Start workout'
                                "
                                @click="startWorkout(routine.id, 'normal')"
                            >
                                Start
                            </button>
                            <button
                                type="button"
                                class="rounded-full border border-border px-5 py-2.5 text-sm text-foreground/80 disabled:opacity-40"
                                :disabled="!canStart(routine)"
                                :title="
                                    !routine.can_start
                                        ? 'Add exercises first'
                                        : data.in_progress_workout
                                          ? 'Finish or resume the current workout'
                                          : 'Start deload'
                                "
                                @click="startWorkout(routine.id, 'deload')"
                            >
                                Deload
                            </button>
                        </div>
                        <div class="flex items-center gap-1">
                            <Link
                                :href="route('routines.edit', routine.id)"
                                class="rounded p-2 text-muted-foreground transition-colors hover:text-primary"
                                title="Edit routine"
                                aria-label="Edit routine"
                            >
                                <Pencil class="size-5" />
                            </Link>
                            <button
                                type="button"
                                class="rounded p-2 text-destructive transition-opacity hover:opacity-80"
                                title="Delete routine"
                                aria-label="Delete routine"
                                @click="deleteRoutine(routine)"
                            >
                                <Trash2 class="size-5" />
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <p v-if="!props.data.routines.length" class="text-sm text-muted-foreground">No routines yet. Tap Create to start one.</p>
        </div>
    </AppLayout>
</template>
