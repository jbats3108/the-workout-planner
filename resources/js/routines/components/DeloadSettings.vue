<script setup lang="ts">
import { useRoutineEditor } from '@/routines/composables/useRoutineEditor';
import { formatDeloadSummary } from '@/routines/lib/deload';
import { ChevronDown } from 'lucide-vue-next';
import { computed } from 'vue';

const { variant = 'desktop' } = withDefaults(
    defineProps<{
        variant?: 'desktop' | 'mobile';
    }>(),
    { variant: 'desktop' },
);

const { form, deloadExpanded, toggleDeloadExpanded } = useRoutineEditor();

const summary = computed(() => formatDeloadSummary(form.deload_weight_factor, form.deload_reps_factor));
</script>

<template>
    <section v-if="variant === 'desktop'" class="border-t border-border bg-card/40 px-4 py-3">
        <h3 class="text-sm font-medium">Deload</h3>
        <p class="mt-1 max-w-3xl text-xs text-muted-foreground">
            Used when you start a <strong class="font-medium text-foreground">deload workout</strong> from the dashboard. Multiplies working weight
            and prescribed reps for every exercise on this routine. Your normal working weights stay the same.
        </p>
        <div class="mt-3 grid max-w-xl gap-3 sm:grid-cols-2">
            <label class="block text-sm">
                <span class="text-muted-foreground">Weight multiplier</span>
                <span class="mt-0.5 block text-xs text-muted-foreground">Working kg × factor (e.g. 0.8 → 80%)</span>
                <input
                    v-model.number="form.deload_weight_factor"
                    type="number"
                    step="0.05"
                    min="0"
                    max="2"
                    class="mt-1.5 w-full rounded border border-border bg-card px-2 py-1.5 font-mono text-sm"
                />
            </label>
            <label class="block text-sm">
                <span class="text-muted-foreground">Reps multiplier</span>
                <span class="mt-0.5 block text-xs text-muted-foreground">Target reps × factor (e.g. 0.8 → round down)</span>
                <input
                    v-model.number="form.deload_reps_factor"
                    type="number"
                    step="0.05"
                    min="0"
                    max="2"
                    class="mt-1.5 w-full rounded border border-border bg-card px-2 py-1.5 font-mono text-sm"
                />
            </label>
        </div>
    </section>

    <section v-else class="mt-3 border-t border-border pt-3">
        <button
            type="button"
            class="flex w-full items-center justify-between gap-2 text-left"
            :aria-expanded="deloadExpanded"
            @click="toggleDeloadExpanded"
        >
            <span class="min-w-0">
                <span class="block text-xs text-muted-foreground">Deload <span class="text-muted-foreground/80">(whole routine)</span></span>
                <span class="block truncate font-mono text-sm text-foreground">{{ summary }}</span>
            </span>
            <ChevronDown class="size-4 shrink-0 text-muted-foreground transition-transform" :class="deloadExpanded ? 'rotate-180' : ''" />
        </button>
        <div v-if="deloadExpanded" class="mt-3 space-y-3">
            <p class="text-xs text-muted-foreground">
                Scales every exercise when you start a deload from the dashboard. Normal working weights are not changed.
            </p>
            <label class="block">
                <span class="text-xs text-muted-foreground">Weight multiplier</span>
                <span class="mt-0.5 block text-[11px] text-muted-foreground">Working kg × factor (0.8 = 80%)</span>
                <input
                    v-model.number="form.deload_weight_factor"
                    type="number"
                    step="0.05"
                    min="0"
                    max="2"
                    class="mt-1 w-full rounded-xl border border-border bg-background px-3 py-2 font-mono text-lg"
                />
            </label>
            <label class="block">
                <span class="text-xs text-muted-foreground">Reps multiplier</span>
                <span class="mt-0.5 block text-[11px] text-muted-foreground">Target reps × factor</span>
                <input
                    v-model.number="form.deload_reps_factor"
                    type="number"
                    step="0.05"
                    min="0"
                    max="2"
                    class="mt-1 w-full rounded-xl border border-border bg-background px-3 py-2 font-mono text-lg"
                />
            </label>
        </div>
    </section>
</template>
