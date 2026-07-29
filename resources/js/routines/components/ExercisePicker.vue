<script setup lang="ts">
import { Sheet, SheetContent, SheetDescription, SheetTitle } from '@/components/ui/sheet';
import { useRoutineEditor } from '@/routines/composables/useRoutineEditor';
import { filterExercises } from '@/routines/lib/catalog';
import { ChevronDown } from 'lucide-vue-next';
import { computed, nextTick, ref, watch } from 'vue';

const model = defineModel<number | null>({ required: true });

withDefaults(
    defineProps<{
        /** Highlight when this slot is the editor’s active selection. */
        active?: boolean;
        variant?: 'mobile' | 'desktop';
    }>(),
    { active: false, variant: 'mobile' },
);

const emit = defineEmits<{
    open: [];
}>();

const { catalog, exerciseName } = useRoutineEditor();

const open = ref(false);
const query = ref('');
const searchEl = ref<HTMLInputElement | null>(null);

const matches = computed(() => filterExercises(catalog.value, query.value));

const label = computed(() => {
    if (!catalog.value.length) {
        return 'Loading…';
    }
    return exerciseName(model.value);
});

watch(open, async (isOpen) => {
    if (!isOpen) {
        return;
    }
    emit('open');
    query.value = '';
    await nextTick();
    searchEl.value?.focus();
});

const pick = (id: number) => {
    model.value = id;
    open.value = false;
    query.value = '';
};

/**
 * Bypass Vue v-model’s composition gate — mobile keyboards (esp. Android) keep
 * `composing` true until space/accept, which delayed filtering mid-word.
 */
const syncQuery = (event: Event) => {
    query.value = (event.target as HTMLInputElement).value;
};
</script>

<template>
    <Sheet v-model:open="open">
        <button
            type="button"
            class="flex items-center justify-between gap-2 text-left outline-none focus:border-primary"
            :class="[
                variant === 'mobile'
                    ? 'w-full rounded-xl border border-border bg-background px-3 py-2.5 text-base'
                    : 'w-44 rounded border border-border bg-card px-2 py-1 text-sm',
                active ? 'border-primary' : '',
            ]"
            :disabled="!catalog.length"
            :aria-expanded="open"
            aria-haspopup="dialog"
            @click="open = true"
        >
            <span class="min-w-0 truncate text-foreground">{{ label }}</span>
            <ChevronDown class="size-4 shrink-0 text-muted-foreground" />
        </button>

        <SheetContent
            side="bottom"
            class="flex h-[min(85dvh,40rem)] max-h-[85dvh] flex-col gap-0 border-border p-0 [&>button]:top-3 [&>button]:right-3"
        >
            <div class="border-b border-border px-4 pt-4 pr-12 pb-3">
                <SheetTitle class="text-base font-semibold text-foreground">Choose exercise</SheetTitle>
                <SheetDescription class="sr-only">Search by name or muscle group, then tap a match.</SheetDescription>
                <label class="mt-3 flex flex-col gap-1 text-xs text-muted-foreground">
                    Search
                    <input
                        ref="searchEl"
                        :value="query"
                        type="text"
                        inputmode="search"
                        enterkeyhint="search"
                        autocomplete="off"
                        autocapitalize="off"
                        autocorrect="off"
                        spellcheck="false"
                        placeholder="Name or muscle group…"
                        class="w-full rounded-xl border border-border bg-background px-3 py-2.5 text-base text-foreground outline-none focus:border-primary"
                        @input="syncQuery"
                        @compositionupdate="syncQuery"
                    />
                </label>
                <p class="mt-1 text-xs text-muted-foreground">{{ matches.length }} of {{ catalog.length }}</p>
            </div>

            <ul class="min-h-0 flex-1 divide-y divide-border overflow-y-auto overscroll-contain">
                <li v-for="exercise in matches" :key="exercise.id">
                    <button
                        type="button"
                        class="flex w-full flex-col items-start gap-0.5 px-4 py-3 text-left active:bg-secondary"
                        :class="exercise.id === model ? 'bg-primary/10' : ''"
                        @click="pick(exercise.id)"
                    >
                        <span class="text-sm font-medium text-foreground">{{ exercise.name }}</span>
                        <span class="font-mono text-xs text-muted-foreground">{{ exercise.primary_muscle_group }}</span>
                    </button>
                </li>
                <li v-if="!matches.length" class="px-4 py-8 text-center text-sm text-muted-foreground">
                    {{ catalog.length ? 'No matches.' : 'Loading exercises…' }}
                </li>
            </ul>
        </SheetContent>
    </Sheet>
</template>
