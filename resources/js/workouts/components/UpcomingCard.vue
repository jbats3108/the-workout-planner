<script setup lang="ts">
defineProps<{
    upcoming: {
        exerciseName: string;
        groupLabel: string;
        setNumber: number;
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
    <div class="w-full max-w-sm rounded-xl border border-border bg-card/60 px-4 py-3 text-center">
        <p class="text-xs tracking-wide text-muted-foreground uppercase">Up next</p>
        <p class="mt-1 text-lg font-semibold">{{ upcoming.exerciseName }}</p>
        <p class="mt-1 text-sm text-muted-foreground">
            Block {{ upcoming.blockPosition }} · {{ upcoming.groupLabel }} · Set {{ upcoming.setNumber }}
            <span v-if="upcoming.isDropset"> · Dropset</span>
        </p>
        <p
            v-if="upcoming.weightLabel != null || upcoming.reps != null"
            class="mt-1 font-mono text-sm text-foreground"
        >
            <span v-if="upcoming.weightLabel != null">{{ upcoming.weightLabel }}{{ weightUnit }}</span>
            <span v-if="upcoming.reps != null"> × {{ upcoming.reps }}</span>
        </p>
        <p v-if="upcoming.plateStack" class="mt-2 font-mono text-xs text-muted-foreground">
            {{ upcoming.plateStack }}
        </p>
    </div>
</template>
