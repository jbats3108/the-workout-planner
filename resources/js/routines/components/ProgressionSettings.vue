<script setup lang="ts">
import EditorDisclosure from '@/routines/components/EditorDisclosure.vue';
import ProgressionStyleFields from '@/routines/components/ProgressionStyleFields.vue';
import { useRoutineEditor } from '@/routines/composables/useRoutineEditor';
import { formatProgressionSummary } from '@/routines/lib/progression';
import type { EditorDensity } from '@/routines/types';
import { computed } from 'vue';

const { variant = 'desktop', flush = false } = defineProps<{
    variant?: EditorDensity;
    flush?: boolean;
}>();

const { form, progressionExpanded, toggleProgressionExpanded } = useRoutineEditor();

const summary = computed(() => formatProgressionSummary(form.progression_style, form.progressive_mid_block));
</script>

<template>
    <section v-if="variant === 'desktop'" data-routine-progression class="border-b border-border bg-card/40 px-4 py-3">
        <h3 class="text-sm font-medium">Progression</h3>
        <p class="mt-1 max-w-3xl text-xs text-muted-foreground">
            How this routine ramps mid-session and offers finish bumps. Training Preferences only seed new routines.
        </p>
        <ProgressionStyleFields variant="desktop" />
    </section>

    <EditorDisclosure
        v-else
        data-routine-progression
        :expanded="progressionExpanded"
        :flush="flush"
        label="Progression"
        :summary="summary"
        @toggle="toggleProgressionExpanded"
    >
        <template #label> Progression <span class="text-muted-foreground/80">(this routine)</span> </template>
        <p class="text-xs text-muted-foreground">
            How this routine ramps mid-session and offers finish bumps. Training Preferences only seed new routines.
        </p>
        <ProgressionStyleFields variant="mobile" />
    </EditorDisclosure>
</template>
