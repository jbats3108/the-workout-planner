<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { formatGramsToKg } from '@/lib/plateCalculator';
import type { Bump } from '@/workouts/types';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps<{
    progression: {
        workout_id: number;
        routine_name: string;
        bumps: Bump[];
    };
}>();

const selected = ref<number[]>(props.progression.bumps.map((b) => b.routine_block_exercise_id));

const form = useForm({
    routine_block_exercise_ids: selected.value,
});

const toggle = (id: number) => {
    if (selected.value.includes(id)) {
        selected.value = selected.value.filter((x) => x !== id);
    } else {
        selected.value = [...selected.value, id];
    }
    form.routine_block_exercise_ids = selected.value;
};

const confirm = () => {
    form.routine_block_exercise_ids = selected.value;
    form.post(route('workouts.progression.apply', props.progression.workout_id));
};

const skip = () => {
    router.post(route('workouts.progression.skip', props.progression.workout_id));
};
</script>

<template>
    <Head title="Progression" />

    <AppLayout :breadcrumbs="[{ title: 'Dashboard', href: '/dashboard' }, { title: 'Progression', href: '#' }]">
        <div class="flex flex-1 flex-col gap-6 p-4 text-foreground">
            <div>
                <p class="font-mono text-xs uppercase tracking-wide text-primary">Progression</p>
                <h1 class="mt-1 text-2xl font-semibold tracking-tight">{{ progression.routine_name }}</h1>
                <p class="mt-2 max-w-lg text-sm text-muted-foreground">
                    You hit the progression target. Confirm which working weights to bump — nothing increases until you confirm.
                </p>
            </div>

            <ul class="divide-y divide-border border border-border">
                <li
                    v-for="bump in progression.bumps"
                    :key="bump.routine_block_exercise_id"
                    class="flex items-center gap-3 px-4 py-3"
                >
                    <input
                        :id="`bump-${bump.routine_block_exercise_id}`"
                        type="checkbox"
                        class="size-4 rounded border-border bg-background text-primary focus:ring-primary"
                        :checked="selected.includes(bump.routine_block_exercise_id)"
                        @change="toggle(bump.routine_block_exercise_id)"
                    />
                    <label :for="`bump-${bump.routine_block_exercise_id}`" class="flex min-w-0 flex-1 flex-col gap-0.5">
                        <span class="truncate font-medium">{{ bump.exercise_name }}</span>
                        <span class="font-mono text-xs text-muted-foreground">
                            {{ formatGramsToKg(bump.from_weight_g) }} → {{ formatGramsToKg(bump.to_weight_g) }} kg
                        </span>
                    </label>
                </li>
            </ul>

            <div class="flex flex-wrap gap-3">
                <button
                    type="button"
                    class="rounded-md bg-primary px-5 py-2.5 text-sm font-semibold text-primary-foreground transition-opacity hover:opacity-90 disabled:opacity-40"
                    :disabled="selected.length === 0 || form.processing"
                    @click="confirm"
                >
                    Confirm bumps
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
