<script setup lang="ts">
defineProps<{
    title: string;
    processing: boolean;
}>();

defineEmits<{
    submit: [];
}>();
</script>

<template>
    <form class="space-y-3 rounded-xl border border-border bg-card p-4" @submit.prevent="$emit('submit')">
        <p class="text-sm font-medium">{{ title }}</p>
        <div class="grid gap-3 sm:grid-cols-2">
            <slot />
        </div>
        <button
            type="submit"
            class="rounded-full bg-primary px-4 py-2 text-sm font-semibold text-primary-foreground disabled:opacity-50"
            :disabled="processing"
        >
            Create
        </button>
    </form>
</template>
