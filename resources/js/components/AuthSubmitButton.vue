<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { LoaderCircle } from 'lucide-vue-next';
import type { HTMLAttributes } from 'vue';

withDefaults(
    defineProps<{
        processing: boolean;
        tabindex?: number | string;
        class?: HTMLAttributes['class'];
        variant?: 'default' | 'secondary' | 'destructive' | 'outline' | 'ghost' | 'link';
    }>(),
    {
        variant: 'default',
    },
);
</script>

<template>
    <Button type="submit" :variant="variant" :class="['w-full', $props.class]" :tabindex="tabindex" :disabled="processing">
        <LoaderCircle v-if="processing" class="h-4 w-4 animate-spin" />
        <slot />
    </Button>
</template>
