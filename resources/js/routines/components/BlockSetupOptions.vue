<script setup lang="ts">
import { useRoutineEditor } from '@/routines/composables/useRoutineEditor';
import { canSetupAfterBlock } from '@/routines/lib/blocks';
import { computed } from 'vue';

const { blockIndex, variant = 'desktop' } = defineProps<{
    blockIndex: number;
    variant?: 'desktop' | 'mobile';
}>();

const { form, toggleSuperset } = useRoutineEditor();

const block = computed(() => form.blocks[blockIndex]);
const canSetupAfter = computed(() => canSetupAfterBlock(blockIndex, form.blocks.length));
const setupAfterLabel = computed(() => (variant === 'desktop' ? 'Setup→next' : 'Setup after exercise'));
</script>

<template>
    <div :class="variant === 'desktop' ? 'flex flex-col gap-1 text-xs' : 'flex flex-wrap gap-4 text-sm'">
        <label :class="variant === 'desktop' ? 'flex items-center gap-1.5 whitespace-nowrap' : 'flex items-center gap-2'">
            <input type="checkbox" :checked="block.is_superset" @change="toggleSuperset(block)" />
            Superset
        </label>
        <label
            :class="[
                variant === 'desktop' ? 'flex items-center gap-1.5 whitespace-nowrap' : 'flex items-center gap-2',
                block.warm_up.steps.length ? '' : 'opacity-40',
            ]"
            :title="block.warm_up.steps.length ? undefined : 'Add warm-up steps first'"
        >
            <input v-model="form.blocks[blockIndex].has_setup_after_warm_up" type="checkbox" :disabled="!block.warm_up.steps.length" />
            Setup before working
        </label>
        <label
            :class="[
                variant === 'desktop' ? 'flex items-center gap-1 whitespace-nowrap' : 'flex items-center gap-2',
                canSetupAfter ? '' : 'opacity-40',
            ]"
            :title="canSetupAfter ? undefined : 'Not on the final exercise'"
        >
            <input v-model="form.blocks[blockIndex].has_setup_after" type="checkbox" :disabled="!canSetupAfter" />
            {{ setupAfterLabel }}
        </label>
    </div>
</template>
