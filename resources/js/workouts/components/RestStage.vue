<script setup lang="ts">
import UpcomingCard from '@/workouts/components/UpcomingCard.vue';
import { useWorkoutPlayer } from '@/workouts/composables/useWorkoutPlayer';

const { restLabel, upcoming, workout, skipRest } = useWorkoutPlayer();
</script>

<template>
    <div class="flex flex-1 flex-col items-center justify-center gap-4 px-6 text-center">
        <p class="text-sm tracking-widest text-muted-foreground uppercase">Rest</p>
        <p class="font-mono text-6xl font-semibold text-primary">{{ restLabel }}</p>
        <UpcomingCard v-if="upcoming" class="mt-2" :upcoming="upcoming" :weight-unit="workout.weight_unit" />
        <button type="button" class="rounded-full border border-border px-5 py-2 text-sm" @click="skipRest">Skip</button>
    </div>
</template>
