<script setup lang="ts">
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
    <div :class="d.fieldsGrid">
        <label :class="d.fieldLabel">
            <span :class="d.fieldTitle">Weight multiplier</span>
            <span :class="d.fieldHint">{{ d.weightHint }}</span>
            <input v-model.number="form.deload_weight_factor" type="number" step="0.05" min="0" max="2" :class="d.input" />
        </label>
        <label :class="d.fieldLabel">
            <span :class="d.fieldTitle">Reps multiplier</span>
            <span :class="d.fieldHint">{{ d.repsHint }}</span>
            <input v-model.number="form.deload_reps_factor" type="number" step="0.05" min="0" max="2" :class="d.input" />
        </label>
        <label :class="d.fieldLabel">
            <span :class="d.fieldTitle">Every N normals</span>
            <span :class="d.fieldHint">{{ d.everyHint }}</span>
            <input v-model.number="form.deload_every_n" type="number" step="1" min="0" max="99" :class="d.input" />
        </label>
    </div>
</template>
