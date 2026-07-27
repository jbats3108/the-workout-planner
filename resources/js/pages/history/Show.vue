<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import type { WorkoutPayload } from '@/workouts/types';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps<{
    history: {
        workout: WorkoutPayload;
        can_re_evaluate: boolean;
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'History', href: '/history' },
    { title: props.history.workout.routine_name, href: '#' },
];

const workingSets = props.history.workout.blocks.flatMap((block) => block.sets.filter((set) => set.group_type === 'working'));

const forms = Object.fromEntries(
    workingSets.map((set) => [
        set.id,
        useForm({
            reps: set.logged_reps ?? set.target_reps ?? 0,
            weight_kg: set.logged_weight_kg ?? set.target_weight_kg ?? 0,
        }),
    ]),
);

const saveSet = (setId: number) => {
    forms[setId].put(route('history.sets.update', [props.history.workout.id, setId]));
};
</script>

<template>
    <Head :title="`History · ${history.workout.routine_name}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-6 p-4 text-foreground">
            <div>
                <p class="font-mono text-xs tracking-wide text-primary uppercase">History</p>
                <h1 class="mt-1 text-2xl font-semibold tracking-tight">{{ history.workout.routine_name }}</h1>
                <p class="mt-1 font-mono text-xs text-muted-foreground">{{ history.workout.mode }}</p>
            </div>

            <div v-for="block in history.workout.blocks" :key="block.id" class="space-y-4">
                <div v-for="set in block.sets" :key="set.id" class="rounded-xl border border-border px-4 py-3">
                    <p class="font-medium">{{ set.exercise_name }}</p>
                    <p class="font-mono text-xs text-muted-foreground uppercase">{{ set.group_type.replace('_', ' ') }}</p>

                    <template v-if="set.group_type === 'warm_up'">
                        <p class="mt-2 text-sm text-muted-foreground">
                            {{ set.logged_reps ?? '—' }} × {{ set.logged_weight_kg ?? set.target_weight_kg ?? '—' }} kg
                        </p>
                    </template>

                    <form v-else class="mt-3 flex flex-wrap items-end gap-3" @submit.prevent="saveSet(set.id)">
                        <label class="flex flex-col gap-1 text-xs text-muted-foreground">
                            Reps
                            <input
                                v-model.number="forms[set.id].reps"
                                type="number"
                                min="0"
                                class="w-20 rounded border border-border bg-background px-2 py-1.5 text-sm"
                            />
                        </label>
                        <label v-if="!set.is_dropset" class="flex flex-col gap-1 text-xs text-muted-foreground">
                            Weight (kg)
                            <input
                                v-model.number="forms[set.id].weight_kg"
                                type="number"
                                min="0"
                                step="0.5"
                                class="w-24 rounded border border-border bg-background px-2 py-1.5 text-sm"
                            />
                        </label>
                        <button
                            type="submit"
                            class="rounded-md bg-primary px-4 py-2 text-sm font-semibold text-primary-foreground disabled:opacity-40"
                            :disabled="forms[set.id].processing"
                        >
                            Save
                        </button>
                    </form>
                </div>
            </div>

            <p v-if="history.can_re_evaluate" class="text-xs text-muted-foreground">
                Edits may update routine weights when this is your latest non-deload finish for this routine.
            </p>
        </div>
    </AppLayout>
</template>
