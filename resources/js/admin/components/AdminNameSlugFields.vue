<script setup lang="ts">
import InputError from '@/components/InputError.vue';

defineProps<{
    name: string;
    slug: string;
    nameError?: string;
    slugError?: string;
}>();

defineEmits<{
    'update:name': [value: string];
    'update:slug': [value: string];
}>();
</script>

<template>
    <label class="flex flex-col gap-1 text-xs text-muted-foreground">
        Name
        <input
            :value="name"
            class="rounded border border-border bg-background px-3 py-2 text-sm text-foreground"
            required
            @input="$emit('update:name', ($event.target as HTMLInputElement).value)"
        />
        <InputError :message="nameError" />
    </label>
    <label class="flex flex-col gap-1 text-xs text-muted-foreground">
        Slug
        <input
            :value="slug"
            class="rounded border border-border bg-background px-3 py-2 font-mono text-sm text-foreground"
            required
            @input="$emit('update:slug', ($event.target as HTMLInputElement).value)"
        />
        <InputError :message="slugError" />
    </label>
</template>
