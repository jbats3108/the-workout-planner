<script setup lang="ts">
defineProps<{
    upcoming: {
        exerciseName: string;
        groupLabel: string;
        setNumber: number;
        setCount: number;
        blockPosition: number;
        weightLabel: string | null;
        reps: number | null;
        isDropset: boolean;
        plateStack: string | null;
    };
    weightUnit: string;
}>();
</script>

<template>
    <div class="w-full max-w-md rounded-xl border border-border bg-card/60 px-5 py-4 text-center">
        <p class="text-base font-medium text-muted-foreground">Up Next:</p>
        <p class="mt-2 text-xl font-semibold">{{ upcoming.exerciseName }}</p>
        <p class="mt-2 text-base text-muted-foreground">
            Block {{ upcoming.blockPosition }} · {{ upcoming.groupLabel }} · Set {{ upcoming.setNumber }}/{{ upcoming.setCount }}
            <span v-if="upcoming.isDropset"> · Dropset</span>
        </p>
        <p v-if="upcoming.weightLabel != null || upcoming.reps != null" class="mt-3 font-mono text-2xl font-semibold text-foreground">
            <span v-if="upcoming.weightLabel != null">{{ upcoming.weightLabel }}{{ weightUnit }}</span>
            <span v-if="upcoming.reps != null"> × {{ upcoming.reps }}</span>
        </p>
        <p v-if="upcoming.plateStack" class="mt-3 font-mono text-base text-muted-foreground">
            {{ upcoming.plateStack }}
        </p>
    </div>
</template>
