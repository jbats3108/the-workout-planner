<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { formatKg, historyRowsForBlock } from '@/workouts/lib/historyDisplay';
import type { PlayerSet, WorkoutPayload } from '@/workouts/types';
import { Head, router, useForm } from '@inertiajs/vue3';
import { Trash2 } from 'lucide-vue-next';
import { computed } from 'vue';

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

const blockRows = computed(() =>
    props.history.workout.blocks.map((block) => ({
        block,
        rows: historyRowsForBlock(block.sets),
    })),
);

const warmUpTitle = (sets: PlayerSet[]) => {
    const names = [...new Set(sets.map((set) => set.exercise_name))];
    return names.length === 1 ? names[0] : 'Warm up';
};

const saveSet = (setId: number) => {
    forms[setId].put(route('history.sets.update', [props.history.workout.id, setId]));
};

const deleteWorkout = () => {
    const name = props.history.workout.routine_name;
    if (!confirm(`Remove “${name}” from history? This cannot be undone.`)) {
        return;
    }
    router.delete(route('history.destroy', props.history.workout.id));
};
</script>

<template>
    <Head :title="`History · ${history.workout.routine_name}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-6 p-4 text-foreground">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <p class="font-mono text-xs tracking-wide text-primary uppercase">History</p>
                    <h1 class="mt-1 text-2xl font-semibold tracking-tight">{{ history.workout.routine_name }}</h1>
                    <p class="mt-1 font-mono text-xs text-muted-foreground">{{ history.workout.mode }}</p>
                </div>
                <button
                    type="button"
                    class="inline-flex items-center gap-1.5 rounded-md bg-destructive px-3 py-2 text-sm font-semibold text-destructive-foreground transition-colors hover:bg-destructive/90"
                    @click="deleteWorkout"
                >
                    <Trash2 class="size-4" />
                    Delete
                </button>
            </div>

            <div v-for="{ block, rows } in blockRows" :key="block.id" class="space-y-4">
                <div v-for="row in rows" :key="row.key" class="rounded-xl border border-border px-4 py-3">
                    <template v-if="row.type === 'warm_up'">
                        <p class="font-medium">{{ warmUpTitle(row.sets) }}</p>
                        <p class="font-mono text-xs text-muted-foreground uppercase">Warm up</p>
                        <ul class="mt-2 space-y-1 text-sm text-muted-foreground">
                            <li v-for="set in row.sets" :key="set.id">
                                <span v-if="warmUpTitle(row.sets) === 'Warm up'" class="text-foreground/80">{{ set.exercise_name }} · </span>
                                {{ set.logged_reps ?? '—' }} × {{ formatKg(set.logged_weight_kg ?? set.target_weight_kg) }} kg
                            </li>
                        </ul>
                    </template>

                    <template v-else>
                        <p class="font-medium">{{ row.set.exercise_name }}</p>
                        <p class="font-mono text-xs text-muted-foreground uppercase">Working</p>

                        <form class="mt-3 flex flex-wrap items-end gap-3" @submit.prevent="saveSet(row.set.id)">
                            <label class="flex flex-col gap-1 text-xs text-muted-foreground">
                                Reps
                                <input
                                    v-model.number="forms[row.set.id].reps"
                                    type="number"
                                    min="0"
                                    class="w-20 rounded border border-border bg-background px-2 py-1.5 text-sm"
                                />
                            </label>
                            <label v-if="!row.set.is_dropset" class="flex flex-col gap-1 text-xs text-muted-foreground">
                                Weight (kg)
                                <input
                                    v-model.number="forms[row.set.id].weight_kg"
                                    type="number"
                                    min="0"
                                    step="0.01"
                                    inputmode="decimal"
                                    class="w-24 rounded border border-border bg-background px-2 py-1.5 text-sm"
                                />
                            </label>
                            <button
                                type="submit"
                                class="rounded-md bg-primary px-4 py-2 text-sm font-semibold text-primary-foreground disabled:opacity-40"
                                :disabled="forms[row.set.id].processing"
                            >
                                Save
                            </button>
                        </form>
                    </template>
                </div>
            </div>

            <p v-if="history.can_re_evaluate" class="text-xs text-muted-foreground">
                Edits may update routine weights when this is your latest non-deload finish for this routine.
            </p>
        </div>
    </AppLayout>
</template>
