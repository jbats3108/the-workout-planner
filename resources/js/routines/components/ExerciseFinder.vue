<script setup lang="ts">
import { useRoutineEditor } from '@/routines/composables/useRoutineEditor';
import { Deferred } from '@inertiajs/vue3';

withDefaults(
    defineProps<{
        /** Desktop header finder wraps Deferred; mobile embeds without it. */
        deferred?: boolean;
        compact?: boolean;
    }>(),
    { deferred: false, compact: false },
);

const {
    exerciseQuery,
    filteredExercises,
    findMatches,
    catalog,
    activeBlock,
    activeExerciseIndex,
    applyExercisePick,
} = useRoutineEditor();
</script>

<template>
    <Deferred v-if="deferred" data="exercises">
        <template #fallback>
            <div class="animate-pulse space-y-2">
                <div class="h-4 w-40 rounded bg-secondary" />
                <div class="h-10 max-w-md rounded-xl bg-secondary" />
            </div>
        </template>
        <label class="flex flex-col gap-1 text-xs text-muted-foreground">
            Find exercise
            <input
                v-model="exerciseQuery"
                type="search"
                placeholder="Name or muscle group…"
                class="w-full max-w-md rounded-xl border border-border bg-card px-3 py-2 text-sm text-foreground outline-none focus:border-primary"
            />
        </label>
        <p class="mt-1 text-xs text-muted-foreground">
            Showing {{ filteredExercises.length }} of {{ catalog.length }}
            <span v-if="exerciseQuery.trim() && activeBlock"> · tap to set selected exercise</span>
        </p>
        <ul
            v-if="findMatches.length"
            class="mt-2 max-h-48 max-w-md overflow-y-auto divide-y divide-border rounded-xl border border-border"
        >
            <li v-for="exercise in findMatches" :key="exercise.id">
                <button
                    type="button"
                    class="flex w-full flex-col items-start gap-0.5 px-3 py-2 text-left hover:bg-secondary"
                    :disabled="!activeBlock"
                    @click="applyExercisePick(exercise.id)"
                >
                    <span class="text-sm font-medium text-foreground">{{ exercise.name }}</span>
                    <span class="font-mono text-xs text-muted-foreground">{{
                        exercise.primary_muscle_group
                    }}</span>
                </button>
            </li>
        </ul>
        <p v-else-if="exerciseQuery.trim()" class="mt-2 text-xs text-muted-foreground">No matches.</p>
    </Deferred>
    <div v-else>
        <label class="flex flex-col gap-1 text-xs text-muted-foreground">
            Find exercise
            <input
                v-model="exerciseQuery"
                type="search"
                placeholder="Name or muscle group…"
                class="w-full rounded-xl border border-border bg-background px-3 py-2.5 text-base text-foreground outline-none focus:border-primary"
            />
        </label>
        <p v-if="compact && activeBlock?.is_superset" class="mt-1 text-xs text-muted-foreground">
            Sets {{ activeExerciseIndex === 0 ? 'A' : 'B' }} · tap a match or focus a slot below
        </p>
        <ul
            v-if="findMatches.length"
            class="mt-2 max-h-40 overflow-y-auto divide-y divide-border rounded-xl border border-border"
        >
            <li v-for="exercise in findMatches" :key="exercise.id">
                <button
                    type="button"
                    class="flex w-full flex-col items-start gap-0.5 px-3 py-2.5 text-left active:bg-secondary"
                    @click="applyExercisePick(exercise.id)"
                >
                    <span class="text-sm font-medium text-foreground">{{ exercise.name }}</span>
                    <span class="font-mono text-xs text-muted-foreground">{{
                        exercise.primary_muscle_group
                    }}</span>
                </button>
            </li>
        </ul>
        <p v-else-if="exerciseQuery.trim()" class="mt-2 text-xs text-muted-foreground">No matches.</p>
    </div>
</template>
