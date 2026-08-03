<script setup lang="ts">
import { useRoutineEditor } from '@/routines/composables/useRoutineEditor';
import { dropsetEditorDensity } from '@/routines/lib/editorDensity';
import type { Block, EditorDensity } from '@/routines/types';
import { computed } from 'vue';

const { block, variant = 'desktop' } = withDefaults(
    defineProps<{
        block: Block;
        variant?: EditorDensity;
    }>(),
    { variant: 'desktop' },
);

const d = computed(() => dropsetEditorDensity[variant]);

const { isDropsetSlot, setSlotKind, dropsetForIndex, removeDropsetSegment, addDropsetSegment, rackStart, rackEnd, rackStep, applyRunTheRack } =
    useRoutineEditor();
</script>

<template>
    <div v-for="setIndex in block.working.set_count" :key="setIndex" :class="d.card">
        <div class="mb-2 flex items-center justify-between gap-2">
            <span :class="d.setLabel">Set {{ setIndex }}</span>
            <select
                :class="d.select"
                :value="isDropsetSlot(block, setIndex - 1) ? 'dropset' : 'normal'"
                @change="setSlotKind(block, setIndex - 1, ($event.target as HTMLSelectElement).value as 'normal' | 'dropset')"
            >
                <option value="normal">Normal</option>
                <option value="dropset">Dropset</option>
            </select>
        </div>
        <template v-if="isDropsetSlot(block, setIndex - 1)">
            <p class="mb-2 text-xs text-muted-foreground">Shared reps: {{ block.exercises[0]?.prescribed_reps ?? '—' }}</p>
            <div v-for="(seg, si) in dropsetForIndex(block, setIndex - 1)!.segments" :key="si" :class="d.segmentRow">
                <input v-model.number="seg.weight_kg" type="number" step="0.01" min="0" inputmode="decimal" :class="d.weightInput" />
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
            <div :class="d.addDropContainer">
                <button type="button" :class="d.addDropButton" @click="addDropsetSegment(block, setIndex - 1)">+ Drop</button>
            </div>
            <div class="mt-3 border-t border-border pt-2">
                <p class="mb-1 text-xs text-muted-foreground">Run the rack</p>
                <div :class="d.rackControls">
                    <label :class="d.rackLabel">
                        Start
                        <input v-model.number="rackStart" type="number" step="0.01" min="0" inputmode="decimal" :class="d.rackInput" />
                    </label>
                    <label :class="d.rackLabel">
                        End
                        <input v-model.number="rackEnd" type="number" step="0.01" min="0" inputmode="decimal" :class="d.rackInput" />
                    </label>
                    <label :class="d.rackLabel">
                        Step
                        <input v-model.number="rackStep" type="number" step="0.5" min="0.5" :class="d.rackInput" />
                    </label>
                    <button type="button" :class="d.rackFillButton" @click="applyRunTheRack(block, setIndex - 1)">
                        {{ d.rackFillLabel }}
                    </button>
                </div>
            </div>
        </template>
    </div>
</template>
