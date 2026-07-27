<script setup lang="ts">
/**
 * Workout player — chrome-minimal full-bleed stage.
 */
import CompleteStage from '@/workouts/components/CompleteStage.vue';
import PlayerHeader from '@/workouts/components/PlayerHeader.vue';
import RestStage from '@/workouts/components/RestStage.vue';
import SetStage from '@/workouts/components/SetStage.vue';
import SetupStage from '@/workouts/components/SetupStage.vue';
import {
    createWorkoutPlayer,
    workoutPlayerKey,
    type PlayWorkoutProps,
} from '@/workouts/composables/useWorkoutPlayer';
import { Head } from '@inertiajs/vue3';
import { provide } from 'vue';

const props = defineProps<PlayWorkoutProps>();

const player = createWorkoutPlayer(props);
provide(workoutPlayerKey, player);

const { workout, focus, current, currentBlock, restSecondsLeft } = player;
</script>

<template>
    <div
        class="safe-pt safe-pb safe-px mx-auto flex min-h-dvh w-full max-w-lg flex-col overscroll-none bg-background text-foreground"
    >
        <Head :title="`Play · ${workout.routine_name}`" />

        <PlayerHeader />

        <RestStage v-if="restSecondsLeft > 0" />
        <SetupStage v-else-if="focus.kind === 'setup' && currentBlock" />
        <CompleteStage v-else-if="focus.kind === 'done'" />
        <SetStage v-else-if="current" />
    </div>
</template>
