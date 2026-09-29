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
    <label :class="d.fieldLabel">
        <span :class="d.fieldTitle">Every N standards</span>
        <span :class="d.fieldHint">{{ d.everyHint }}</span>
        <input v-model.number="form.deload_every_n" type="number" step="1" min="0" max="99" :class="d.input" />
    </label>
</template>
