<script setup lang="ts">
import { useFlashError, useFlashSuccess } from '@/shared/composables/useFlashSuccess';

defineOptions({ inheritAttrs: false });

const successMessage = useFlashSuccess();
const errorMessage = useFlashError();
</script>

<template>
    <div v-if="successMessage || errorMessage" class="mb-4 flex flex-col gap-2" v-bind="$attrs">
        <div v-if="successMessage" class="rounded-xl border border-primary/40 bg-primary/10 px-4 py-3 text-sm text-primary" role="status">
            {{ successMessage }}
        </div>
        <div v-if="errorMessage" class="rounded-xl border border-destructive/40 bg-destructive/10 px-4 py-3 text-sm text-destructive" role="alert">
            {{ errorMessage }}
        </div>
    </div>
</template>
