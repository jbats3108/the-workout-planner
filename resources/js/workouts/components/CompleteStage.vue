<script setup lang="ts">
import { useWorkoutPlayer } from '@/workouts/composables/useWorkoutPlayer';

const { workout, finishWorkout } = useWorkoutPlayer();
</script>

<template>
    <div class="flex flex-1 flex-col items-center justify-center gap-6 px-6">
        <p class="text-sm tracking-widest text-muted-foreground uppercase">Complete</p>
        <p class="text-center text-2xl font-semibold">All sets logged</p>
        <button
            type="button"
            class="rounded-full bg-primary px-8 py-3 text-sm font-semibold text-primary-foreground"
            :disabled="workout.status !== 'in_progress'"
            @click="finishWorkout"
        >
            Finish workout
        </button>
    </div>
</template>
