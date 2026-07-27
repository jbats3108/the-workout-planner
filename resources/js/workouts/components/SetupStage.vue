<script setup lang="ts">
import UpcomingCard from '@/workouts/components/UpcomingCard.vue';
import { useWorkoutPlayer } from '@/workouts/composables/useWorkoutPlayer';

const { setupHint, upcoming, workout, acknowledgeSetup } = useWorkoutPlayer();
</script>

<template>
    <div class="flex flex-1 flex-col items-center justify-center gap-6 px-6">
        <p class="text-sm tracking-widest text-muted-foreground uppercase">Setup</p>
        <p class="text-center text-2xl font-semibold">Change equipment, then continue</p>
        <p class="text-sm text-muted-foreground">{{ setupHint }}</p>
        <UpcomingCard v-if="upcoming" :upcoming="upcoming" :weight-unit="workout.weight_unit" />
        <button type="button" class="rounded-full bg-primary px-8 py-3 text-sm font-semibold text-primary-foreground" @click="acknowledgeSetup">
            Setup done
        </button>
    </div>
</template>
