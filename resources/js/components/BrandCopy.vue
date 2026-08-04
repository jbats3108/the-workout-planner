<script setup lang="ts">
import { computed } from 'vue';

const props = defineProps<{
    text: string;
}>();

const html = computed(() => {
    const escaped = props.text.replaceAll('&', '&amp;').replaceAll('<', '&lt;').replaceAll('>', '&gt;');

    return escaped.replaceAll(
        'OVRLOAD',
        '<span class="font-semibold tracking-wide"><span class="text-primary">OVR</span><span class="text-foreground">LOAD</span></span>',
    );
});
</script>

<template>
    <!-- Trusted static marketing copy only — not user input. -->
    <span v-html="html" />
</template>
