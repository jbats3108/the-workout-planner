<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import type { Routine } from '@/routines/types';
import { useFlashSuccess } from '@/shared/composables/useFlashSuccess';
import { type BreadcrumbItem } from '@/types';
import type { InProgressWorkout } from '@/workouts/types';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { Pencil, Trash2 } from 'lucide-vue-next';
import { computed } from 'vue';

const props = defineProps<{
    data: {
        routines: Routine[];
        in_progress_workout: InProgressWorkout | null;
    };
}>();

const page = usePage();
const formErrors = computed(() => Object.values(page.props.errors ?? {}));
const successMessage = useFlashSuccess();

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
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl bg-background p-4 text-foreground">
            <div v-if="successMessage" class="rounded-xl border border-primary/40 bg-primary/10 px-4 py-3 text-sm text-primary" role="status">
                {{ successMessage }}
            </div>

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

            <div class="flex flex-wrap items-center justify-between gap-3">
                <h2 class="text-xl font-semibold">My Routines</h2>
                <Link :href="route('routines.create')" class="rounded-full bg-primary px-4 py-2 text-sm font-semibold text-primary-foreground">
                    Create
                </Link>
            </div>

            <div class="grid auto-rows-min gap-3 md:grid-cols-3">
                <div v-for="routine in props.data.routines" :key="routine.id" class="relative rounded-xl border border-border bg-card p-4 pb-10">
                    <Link :href="route('routines.edit', routine.id)" class="block transition hover:text-primary">
                        <h3 class="text-lg font-semibold">{{ routine.name }}</h3>
                        <p class="mt-1 font-mono text-xs text-muted-foreground">
                            Deload {{ routine.deload_weight_factor }}w / {{ routine.deload_reps_factor }}r
                        </p>
                    </Link>
                    <p v-if="!routine.can_start" class="mt-3 text-xs text-muted-foreground">Add exercises in the editor before starting.</p>
                    <div class="mt-4 flex flex-wrap gap-2">
                        <button
                            type="button"
                            class="rounded-full bg-primary px-4 py-2 text-xs font-semibold text-primary-foreground disabled:opacity-40"
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
                            class="rounded-full border border-border px-4 py-2 text-xs text-foreground/80 disabled:opacity-40"
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
                    <div class="absolute right-3 bottom-3 flex items-center gap-1">
                        <Link
                            :href="route('routines.edit', routine.id)"
                            class="rounded p-1 text-muted-foreground transition-colors hover:text-primary"
                            title="Edit routine"
                            aria-label="Edit routine"
                        >
                            <Pencil class="size-3.5" />
                        </Link>
                        <button
                            type="button"
                            class="rounded p-1 text-destructive transition-opacity hover:opacity-80"
                            title="Delete routine"
                            aria-label="Delete routine"
                            @click="deleteRoutine(routine)"
                        >
                            <Trash2 class="size-3.5" />
                        </button>
                    </div>
                </div>
            </div>
            <p v-if="!props.data.routines.length" class="text-sm text-muted-foreground">No routines yet. Tap Create to start one.</p>
        </div>
    </AppLayout>
</template>
