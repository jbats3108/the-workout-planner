<script setup lang="ts">
import { gramsToKg, type PlateLoadResult } from '@/lib/plateCalculator';

defineProps<{
    plateLoad: PlateLoadResult;
    formatPlateStack: string;
    weightUnit: string;
}>();

defineEmits<{
    applyNearest: [];
}>();
</script>

<template>
    <div class="rounded-xl border border-border bg-card/60 px-4 py-3 text-sm">
        <p class="text-xs tracking-wide text-muted-foreground uppercase">Plates</p>
        <p class="mt-1 font-mono text-foreground">{{ formatPlateStack }}</p>
        <p v-if="!plateLoad.exact" class="mt-1 text-xs text-muted-foreground">
            Nearest loadable:
            {{ gramsToKg(plateLoad.total_g) }}{{ weightUnit }}
            <span v-if="plateLoad.delta_g > 0">(+{{ gramsToKg(plateLoad.delta_g) }})</span>
            <span v-else-if="plateLoad.delta_g < 0">({{ gramsToKg(plateLoad.delta_g) }})</span>
        </p>
        <button
            v-if="!plateLoad.exact"
            type="button"
            class="mt-3 rounded-full border border-primary/40 bg-primary/10 px-4 py-2 text-sm font-medium text-primary"
            @click="$emit('applyNearest')"
        >
            Use nearest
        </button>
    </div>
</template>
