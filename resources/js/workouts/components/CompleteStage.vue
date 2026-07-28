<script setup lang="ts">
import { useWorkoutPlayer } from '@/workouts/composables/useWorkoutPlayer';

const { workout, finishWorkout, abandonWorkout, leaveWorkout } = useWorkoutPlayer();
</script>

<template>
    <div class="flex min-h-0 flex-1 flex-col">
        <div class="flex shrink-0 items-center justify-end gap-2 px-4 py-3">
            <button
                v-if="workout.status === 'in_progress'"
                type="button"
                class="rounded-md border border-destructive/40 px-3 py-1.5 text-sm text-destructive"
                @click="abandonWorkout"
            >
                Abandon
            </button>
            <button
                type="button"
                class="rounded-md border border-border px-3 py-1.5 text-sm text-muted-foreground hover:text-foreground"
                @click="leaveWorkout"
            >
                Leave
            </button>
        </div>

        <div class="flex flex-1 flex-col items-center justify-center gap-6 px-6 pb-16">
            <p class="text-sm tracking-widest text-muted-foreground uppercase">Complete</p>
            <p class="text-center text-3xl font-semibold tracking-tight">All sets logged</p>
            <p class="max-w-xs text-center text-sm text-muted-foreground">{{ workout.routine_name }}</p>
            <button
                type="button"
                class="rounded-full bg-primary px-10 py-4 text-base font-semibold text-primary-foreground"
                :disabled="workout.status !== 'in_progress'"
                @click="finishWorkout"
            >
                Finish workout
            </button>
        </div>
    </div>
</template>
