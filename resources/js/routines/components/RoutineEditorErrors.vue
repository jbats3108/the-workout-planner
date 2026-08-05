<script setup lang="ts">
import { useRoutineEditor } from '@/routines/composables/useRoutineEditor';

const { errorList } = useRoutineEditor();
</script>

<template>
    <div
        v-if="errorList.length"
        data-routine-save-errors
        class="space-y-1 rounded-xl border border-destructive/40 bg-destructive/10 px-4 py-3 text-sm text-destructive"
        role="alert"
    >
        <p class="font-semibold">Couldn't save</p>
        <p v-for="(message, index) in errorList" :key="index">{{ message }}</p>
    </div>
</template>
