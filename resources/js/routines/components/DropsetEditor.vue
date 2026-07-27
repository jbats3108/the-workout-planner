<script setup lang="ts">
import { useRoutineEditor } from '@/routines/composables/useRoutineEditor';
import type { Block } from '@/routines/types';

withDefaults(
    defineProps<{
        block: Block;
        variant?: 'desktop' | 'mobile';
    }>(),
    { variant: 'desktop' },
);

const {
    isDropsetSlot,
    setSlotKind,
    dropsetForIndex,
    removeDropsetSegment,
    addDropsetSegment,
    rackStart,
    rackEnd,
    rackStep,
    applyRunTheRack,
} = useRoutineEditor();
</script>

<template>
    <div
        v-for="setIndex in block.working.set_count"
        :key="setIndex"
        :class="
            variant === 'desktop'
                ? 'rounded-lg border border-border bg-background p-3'
                : 'rounded-xl border border-border bg-background p-3'
        "
    >
        <div class="mb-2 flex items-center justify-between gap-2">
            <span
                :class="
                    variant === 'desktop'
                        ? 'text-xs font-medium text-muted-foreground'
                        : 'text-sm font-medium'
                "
                >Set {{ setIndex }}</span
            >
            <select
                :class="
                    variant === 'desktop'
                        ? 'rounded border border-border bg-card px-2 py-1 text-xs'
                        : 'rounded-lg border border-border bg-card px-2 py-1 text-sm'
                "
                :value="isDropsetSlot(block, setIndex - 1) ? 'dropset' : 'normal'"
                @change="
                    setSlotKind(
                        block,
                        setIndex - 1,
                        ($event.target as HTMLSelectElement).value as 'normal' | 'dropset',
                    )
                "
            >
                <option value="normal">Normal</option>
                <option value="dropset">Dropset</option>
            </select>
        </div>
        <template v-if="isDropsetSlot(block, setIndex - 1)">
            <p class="mb-2 text-xs text-muted-foreground">
                Shared reps: {{ block.exercises[0]?.prescribed_reps ?? '—' }}
            </p>
            <div
                v-for="(seg, si) in dropsetForIndex(block, setIndex - 1)!.segments"
                :key="si"
                :class="variant === 'desktop' ? 'mb-1 flex items-center gap-1' : 'mb-1.5 flex items-center gap-2'"
            >
                <input
                    v-model.number="seg.weight_kg"
                    type="number"
                    step="0.01"
                    min="0"
                    inputmode="decimal"
                    :class="
                        variant === 'desktop'
                            ? 'w-20 rounded border border-border bg-card px-2 py-1 font-mono text-sm'
                            : 'w-24 rounded-lg border border-border bg-card px-2 py-1.5 font-mono text-base'
                    "
                />
                <span class="text-xs text-muted-foreground">kg</span>
                <button
                    type="button"
                    class="ml-auto text-xs text-muted-foreground hover:text-destructive disabled:opacity-30"
                    :disabled="dropsetForIndex(block, setIndex - 1)!.segments.length <= 2"
                    @click="removeDropsetSegment(block, setIndex - 1, si)"
                >
                    −
                </button>
            </div>
            <div v-if="variant === 'desktop'" class="mt-2 flex flex-wrap items-center gap-2">
                <button
                    type="button"
                    class="text-xs text-primary"
                    @click="addDropsetSegment(block, setIndex - 1)"
                >
                    + Drop
                </button>
            </div>
            <button
                v-else
                type="button"
                class="mt-1 text-xs text-primary"
                @click="addDropsetSegment(block, setIndex - 1)"
            >
                + Drop
            </button>
            <div class="mt-3 border-t border-border pt-2">
                <p class="mb-1 text-xs text-muted-foreground">Run the rack</p>
                <div v-if="variant === 'desktop'" class="flex flex-wrap items-end gap-1">
                    <label class="text-[10px] text-muted-foreground">
                        Start
                        <input
                            v-model.number="rackStart"
                            type="number"
                            step="0.01"
                            min="0"
                            inputmode="decimal"
                            class="mt-0.5 w-16 rounded border border-border bg-card px-1 py-1 font-mono text-xs"
                        />
                    </label>
                    <label class="text-[10px] text-muted-foreground">
                        End
                        <input
                            v-model.number="rackEnd"
                            type="number"
                            step="0.01"
                            min="0"
                            inputmode="decimal"
                            class="mt-0.5 w-16 rounded border border-border bg-card px-1 py-1 font-mono text-xs"
                        />
                    </label>
                    <label class="text-[10px] text-muted-foreground">
                        Step
                        <input
                            v-model.number="rackStep"
                            type="number"
                            step="0.5"
                            min="0.5"
                            class="mt-0.5 w-16 rounded border border-border bg-card px-1 py-1 font-mono text-xs"
                        />
                    </label>
                    <button
                        type="button"
                        class="rounded border border-primary/40 px-2 py-1 text-xs text-primary hover:bg-primary/10"
                        @click="applyRunTheRack(block, setIndex - 1)"
                    >
                        Fill
                    </button>
                </div>
                <template v-else>
                    <div class="grid grid-cols-3 gap-2">
                        <label class="text-[10px] text-muted-foreground">
                            Start
                            <input
                                v-model.number="rackStart"
                                type="number"
                                step="0.01"
                                min="0"
                                inputmode="decimal"
                                class="mt-0.5 w-full rounded-lg border border-border bg-card px-2 py-1.5 font-mono text-sm"
                            />
                        </label>
                        <label class="text-[10px] text-muted-foreground">
                            End
                            <input
                                v-model.number="rackEnd"
                                type="number"
                                step="0.01"
                                min="0"
                                inputmode="decimal"
                                class="mt-0.5 w-full rounded-lg border border-border bg-card px-2 py-1.5 font-mono text-sm"
                            />
                        </label>
                        <label class="text-[10px] text-muted-foreground">
                            Step
                            <input
                                v-model.number="rackStep"
                                type="number"
                                step="0.5"
                                min="0.5"
                                class="mt-0.5 w-full rounded-lg border border-border bg-card px-2 py-1.5 font-mono text-sm"
                            />
                        </label>
                    </div>
                    <button
                        type="button"
                        class="mt-2 w-full rounded-lg border border-primary/40 py-2 text-xs text-primary"
                        @click="applyRunTheRack(block, setIndex - 1)"
                    >
                        Fill from rack
                    </button>
                </template>
            </div>
        </template>
    </div>
</template>
