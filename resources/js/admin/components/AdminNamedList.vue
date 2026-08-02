<script setup lang="ts" generic="T extends { id: number; name: string; slug: string }">
defineProps<{
    items: T[];
    processing: boolean;
}>();

defineEmits<{
    delete: [item: T];
}>();
</script>

<template>
    <ul class="divide-y divide-border rounded-xl border border-border">
        <li v-for="item in items" :key="item.id" class="flex flex-wrap items-center justify-between gap-2 px-4 py-3">
            <div>
                <p class="font-medium">{{ item.name }}</p>
                <p class="font-mono text-xs text-muted-foreground">
                    <slot name="meta" :item="item">{{ item.slug }}</slot>
                </p>
            </div>
            <button
                type="button"
                class="text-sm text-destructive hover:underline disabled:opacity-50"
                :disabled="processing"
                @click="$emit('delete', item)"
            >
                Delete
            </button>
        </li>
    </ul>
</template>
