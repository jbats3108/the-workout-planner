<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { formatGramsToKg } from '@/lib/plateCalculator';
import type { Bump, UndoBump } from '@/workouts/types';
import { Head, router, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps<{
    progression: {
        workout_id: string;
        routine_name: string;
        bumps: Bump[];
        undos: UndoBump[];
    };
}>();

const selectedBumps = ref<number[]>(props.progression.bumps.map((b) => b.routine_block_exercise_id));
const selectedUndos = ref<number[]>(props.progression.undos.map((u) => u.bump_record_id));

const form = useForm({});

const hasActions = computed(() => props.progression.bumps.length > 0 || props.progression.undos.length > 0);

const toggleBump = (id: number) => {
    if (selectedBumps.value.includes(id)) {
        selectedBumps.value = selectedBumps.value.filter((x) => x !== id);
    } else {
        selectedBumps.value = [...selectedBumps.value, id];
    }
};

const toggleUndo = (id: number) => {
    if (selectedUndos.value.includes(id)) {
        selectedUndos.value = selectedUndos.value.filter((x) => x !== id);
    } else {
        selectedUndos.value = [...selectedUndos.value, id];
    }
};

const confirm = () => {
    // Omit empty lists — Spatie infers `required`, and Laravel treats [] as empty.
    form.transform(() => ({
        ...(selectedBumps.value.length > 0 ? { routine_block_exercise_ids: selectedBumps.value } : {}),
        ...(selectedUndos.value.length > 0 ? { undo_bump_record_ids: selectedUndos.value } : {}),
    })).post(route('workouts.progression.apply', props.progression.workout_id));
};

const skip = () => {
    router.post(route('workouts.progression.skip', props.progression.workout_id));
};
</script>

<template>
    <Head title="Progression" />

    <AppLayout
        :breadcrumbs="[
            { title: 'Dashboard', href: '/dashboard' },
            { title: 'Progression', href: '#' },
        ]"
    >
        <div class="flex flex-1 flex-col gap-6 p-4 text-foreground">
            <div>
                <p class="font-mono text-xs tracking-wide text-primary uppercase">Progression</p>
                <h1 class="mt-1 text-2xl font-semibold tracking-tight">{{ progression.routine_name }}</h1>
                <p class="mt-2 max-w-lg text-sm text-muted-foreground">
                    <template v-if="progression.bumps.length">Confirm working-weight bumps from this workout.</template>
                    <template v-if="progression.bumps.length && progression.undos.length"> </template>
                    <template v-if="progression.undos.length">Undo bumps that no longer apply after your edit.</template>
                    <template v-if="!hasActions">Nothing to confirm.</template>
                </p>
            </div>

            <div v-if="progression.bumps.length" class="space-y-2">
                <p class="text-sm font-medium">Bump working weights</p>
                <ul class="divide-y divide-border border border-border">
                    <li v-for="bump in progression.bumps" :key="bump.routine_block_exercise_id" class="flex items-center gap-3 px-4 py-3">
                        <input
                            :id="`bump-${bump.routine_block_exercise_id}`"
                            type="checkbox"
                            class="size-4 rounded border-border bg-background text-primary focus:ring-primary"
                            :checked="selectedBumps.includes(bump.routine_block_exercise_id)"
                            @change="toggleBump(bump.routine_block_exercise_id)"
                        />
                        <label :for="`bump-${bump.routine_block_exercise_id}`" class="flex min-w-0 flex-1 flex-col gap-0.5">
                            <span class="truncate font-medium">{{ bump.exercise_name }}</span>
                            <span class="font-mono text-xs text-muted-foreground">
                                {{ formatGramsToKg(bump.from_weight_g) }} → {{ formatGramsToKg(bump.to_weight_g) }} kg
                            </span>
                        </label>
                    </li>
                </ul>
            </div>

            <div v-if="progression.undos.length" class="space-y-2">
                <p class="text-sm font-medium">Undo bumps</p>
                <ul class="divide-y divide-border border border-border">
                    <li v-for="undo in progression.undos" :key="undo.bump_record_id" class="flex items-center gap-3 px-4 py-3">
                        <input
                            :id="`undo-${undo.bump_record_id}`"
                            type="checkbox"
                            class="size-4 rounded border-border bg-background text-primary focus:ring-primary"
                            :checked="selectedUndos.includes(undo.bump_record_id)"
                            @change="toggleUndo(undo.bump_record_id)"
                        />
                        <label :for="`undo-${undo.bump_record_id}`" class="flex min-w-0 flex-1 flex-col gap-0.5">
                            <span class="truncate font-medium">{{ undo.exercise_name }}</span>
                            <span class="font-mono text-xs text-muted-foreground">
                                {{ formatGramsToKg(undo.from_weight_g) }} → {{ formatGramsToKg(undo.to_weight_g) }} kg
                            </span>
                        </label>
                    </li>
                </ul>
            </div>

            <div class="flex flex-wrap gap-3">
                <button
                    type="button"
                    class="rounded-md bg-primary px-5 py-2.5 text-sm font-semibold text-primary-foreground transition-opacity hover:opacity-90 disabled:opacity-40"
                    :disabled="(selectedBumps.length === 0 && selectedUndos.length === 0) || form.processing"
                    @click="confirm"
                >
                    Confirm
                </button>
                <button
                    type="button"
                    class="rounded-md px-5 py-2.5 text-sm font-medium text-muted-foreground transition-colors hover:text-foreground"
                    @click="skip"
                >
                    Skip
                </button>
            </div>
        </div>
    </AppLayout>
</template>
