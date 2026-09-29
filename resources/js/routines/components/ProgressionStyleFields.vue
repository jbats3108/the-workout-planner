<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { useRoutineEditor } from '@/routines/composables/useRoutineEditor';
import { deloadSettingsDensity } from '@/routines/lib/editorDensity';
import type { EditorDensity } from '@/routines/types';
import { computed } from 'vue';

const { variant = 'desktop' } = defineProps<{
    variant?: EditorDensity;
}>();

const d = computed(() => deloadSettingsDensity[variant]);
const { form } = useRoutineEditor();
</script>

<template>
    <fieldset :class="variant === 'desktop' ? 'space-y-2' : 'mt-3 space-y-2'">
        <legend :class="d.fieldTitle">Progression style</legend>
        <span :class="d.fieldHint">
            Controls mid-session ramping and when a finish bump is offered. Snapshotted when a workout starts from this routine.
        </span>
        <label class="flex items-start gap-2 text-sm">
            <input v-model="form.progression_style" type="radio" value="straight_sets" class="mt-0.5" />
            <span>Straight Sets — same weight all block; finish bump if any set hit Target</span>
        </label>
        <label class="flex items-start gap-2 text-sm">
            <input v-model="form.progression_style" type="radio" value="progressive_overload" class="mt-0.5" />
            <span>
                Progressive Overload — bump the next set when Target is hit; finish bump if the final working set was at your top weight and hit
                Target
            </span>
        </label>
        <InputError :message="form.errors.progression_style" />
    </fieldset>

    <fieldset v-if="form.progression_style === 'progressive_overload'" :class="variant === 'desktop' ? 'mt-3 space-y-2' : 'mt-3 space-y-2'">
        <legend :class="d.fieldTitle">Mid-block bump</legend>
        <span :class="d.fieldHint">When a working set hits Target, raise the next set by 2.5 kg automatically or ask on rest.</span>
        <label class="flex items-center gap-2 text-sm">
            <input v-model="form.progressive_mid_block" type="radio" value="ask" />
            Ask on rest
        </label>
        <label class="flex items-center gap-2 text-sm">
            <input v-model="form.progressive_mid_block" type="radio" value="auto" />
            Auto-bump next set
        </label>
        <InputError :message="form.errors.progressive_mid_block" />
    </fieldset>
</template>
