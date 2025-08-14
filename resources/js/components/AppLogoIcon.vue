<script setup lang="ts">
import type { HTMLAttributes } from 'vue';

defineOptions({
    inheritAttrs: false,
});

interface Props {
    className?: HTMLAttributes['class'];
}

defineProps<Props>();
</script>

<template>
        <!-- Barbell icon -->
        <svg
            :class="className"
            :style="{ color: '#000000' }"
            xmlns="http://www.w3.org/2000/svg"
            viewBox="0 0 48 48"
            stroke="currentColor"
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="3"
            role="img"
            aria-label="Barbell icon"
            v-bind="$attrs"
        >
            <!-- Center bar -->
            <path d="M6 24h36" />

            <!-- Left plates: biggest (inner) -> smaller (outer) -->
            <rect x="9"  y="16" width="4" height="16" rx="1.5" />
            <rect x="5"  y="18" width="4" height="12" rx="1.5" />

            <!-- Right plates: biggest (inner) -> smaller (outer) -->
            <rect x="35" y="16" width="4" height="16" rx="1.5" />
            <rect x="39" y="18" width="4" height="12" rx="1.5" />

        </svg>

</template>
