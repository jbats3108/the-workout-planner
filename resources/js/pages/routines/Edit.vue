<script setup lang="ts">
/**
 * Routine editor — desktop: dense list (A), mobile: stage (B), styling: B (dark zinc + lime).
 */
import InputError from '@/components/InputError.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

type ExerciseOption = { id: number; name: string; primary_muscle_group: string };

type BlockExercise = {
    exercise_id: number | null;
    working_weight_kg: number;
    prescribed_reps: number;
    achievement_floor: number | null;
    progression_target: number | null;
};

type Block = {
    is_superset: boolean;
    has_setup_after: boolean;
    exercises: BlockExercise[];
    working: { set_count: number; rest_seconds: number };
    warm_up: { set_count: number; rest_seconds: number; percents: number[] };
};

type RoutinePayload = {
    id: number;
    name: string;
    deload_weight_factor: number;
    deload_reps_factor: number;
    blocks: Block[];
};

const props = defineProps<{
    routine: RoutinePayload;
    exercises: ExerciseOption[];
    weight_unit: string;
}>();

const emptyExercise = (): BlockExercise => ({
    exercise_id: props.exercises[0]?.id ?? null,
    working_weight_kg: 60,
    prescribed_reps: 6,
    achievement_floor: null,
    progression_target: null,
});

const emptyBlock = (superset = false): Block => ({
    is_superset: superset,
    has_setup_after: false,
    exercises: superset ? [emptyExercise(), emptyExercise()] : [emptyExercise()],
    working: { set_count: 3, rest_seconds: 120 },
    warm_up: { set_count: 0, rest_seconds: 60, percents: [] },
});

const form = useForm({
    name: props.routine.name,
    deload_weight_factor: props.routine.deload_weight_factor,
    deload_reps_factor: props.routine.deload_reps_factor,
    blocks: props.routine.blocks.length ? structuredClone(props.routine.blocks) : ([] as Block[]),
});

const active = ref(0);
watch(
    () => form.blocks.length,
    (len) => {
        if (active.value >= len) {
            active.value = Math.max(0, len - 1);
        }
    },
);

const activeBlock = computed(() => form.blocks[active.value] ?? null);

const warmUpText = (block: Block) => block.warm_up.percents.join(', ');
const setWarmUpText = (block: Block, value: string) => {
    block.warm_up.percents = value
        .split(/[,\s]+/)
        .map((p) => parseInt(p, 10))
        .filter((n) => !Number.isNaN(n) && n > 0);
    block.warm_up.set_count = block.warm_up.percents.length;
};

const formatRest = (seconds: number) => {
    if (seconds < 60) return `${seconds}s`;
    const m = Math.floor(seconds / 60);
    const s = seconds % 60;
    return s ? `${m}m ${s}s` : `${m}m`;
};

const exerciseName = (id: number | null) => props.exercises.find((e) => e.id === id)?.name ?? 'Exercise';

const addBlock = (superset = false) => {
    form.blocks.push(emptyBlock(superset));
    active.value = form.blocks.length - 1;
};

const removeBlock = (index: number) => {
    form.blocks.splice(index, 1);
};

const toggleSuperset = (block: Block) => {
    block.is_superset = !block.is_superset;
    if (block.is_superset && block.exercises.length < 2) {
        block.exercises.push(emptyExercise());
    }
    if (!block.is_superset && block.exercises.length > 1) {
        block.exercises = [block.exercises[0]];
    }
};

const save = () => {
    form.put(route('routines.update', props.routine.id), { preserveScroll: true });
};

const errorList = computed(() => Object.values(form.errors));
</script>

<template>
    <AppLayout :breadcrumbs="[{ title: 'Dashboard', href: '/dashboard' }, { title: form.name || 'Routine', href: '#' }]">
        <Head :title="`Edit · ${form.name}`" />

        <div class="flex min-h-[calc(100vh-8rem)] flex-1 flex-col overflow-x-auto bg-background text-foreground">
            <!-- Shared header -->
            <header class="border-b border-border px-4 py-4 md:px-6">
                <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
                    <div class="min-w-0 flex-1">
                        <p class="text-xs tracking-[0.2em] text-muted-foreground uppercase">Routine</p>
                        <input
                            v-model="form.name"
                            class="mt-1 w-full border-0 border-b border-border bg-transparent text-2xl font-bold outline-none focus:border-primary"
                            required
                        />
                        <InputError :message="form.errors.name" />
                    </div>
                    <div class="flex flex-wrap gap-3 font-mono text-sm">
                        <label class="flex items-center gap-2 text-muted-foreground">
                            Deload ×W
                            <input
                                v-model.number="form.deload_weight_factor"
                                type="number"
                                step="0.1"
                                min="0"
                                class="w-16 rounded border border-border bg-card px-2 py-1 text-foreground"
                            />
                        </label>
                        <label class="flex items-center gap-2 text-muted-foreground">
                            ×R
                            <input
                                v-model.number="form.deload_reps_factor"
                                type="number"
                                step="0.1"
                                min="0"
                                class="w-16 rounded border border-border bg-card px-2 py-1 text-foreground"
                            />
                        </label>
                        <button
                            type="button"
                            class="rounded-full bg-primary px-4 py-2 text-sm font-semibold text-primary-foreground disabled:opacity-50"
                            :disabled="form.processing"
                            @click="save"
                        >
                            Save
                        </button>
                    </div>
                </div>
                <InputError class="mt-2" :message="form.errors.blocks" />
                <div v-if="errorList.length" class="mt-2 space-y-1 text-sm text-destructive">
                    <p v-for="(message, index) in errorList" :key="index">{{ message }}</p>
                </div>
                <p v-if="form.recentlySuccessful" class="mt-2 text-sm text-primary">Saved.</p>
            </header>

            <!-- Desktop: dense list (A structure) -->
            <div class="hidden flex-1 flex-col md:flex">
                <div class="flex-1 overflow-x-auto px-2 py-3">
                    <table class="w-full min-w-[56rem] border-collapse text-left text-sm">
                        <thead>
                            <tr class="border-b border-border font-mono text-xs uppercase text-muted-foreground">
                                <th class="px-2 py-2">#</th>
                                <th class="px-2 py-2">Exercise</th>
                                <th class="px-2 py-2">kg</th>
                                <th class="px-2 py-2">Reps</th>
                                <th class="px-2 py-2">Sets</th>
                                <th class="px-2 py-2">Rest</th>
                                <th class="px-2 py-2">Warm-up %</th>
                                <th class="px-2 py-2">Flags</th>
                                <th class="px-2 py-2" />
                            </tr>
                        </thead>
                        <tbody>
                            <template v-for="(block, bi) in form.blocks" :key="bi">
                                <tr
                                    v-for="(ex, ei) in block.exercises"
                                    :key="`${bi}-${ei}`"
                                    class="border-b border-border"
                                >
                                    <td class="px-2 py-2 font-mono text-muted-foreground">{{ ei === 0 ? bi + 1 : '' }}</td>
                                    <td class="px-2 py-2">
                                        <div class="flex items-center gap-2">
                                            <span v-if="block.is_superset" class="font-mono text-xs text-primary">{{ ei === 0 ? 'A' : 'B' }}</span>
                                            <select
                                                v-model.number="ex.exercise_id"
                                                class="max-w-xs rounded border border-border bg-card px-2 py-1"
                                            >
                                                <option v-for="opt in exercises" :key="opt.id" :value="opt.id">{{ opt.name }}</option>
                                            </select>
                                        </div>
                                    </td>
                                    <td class="px-2 py-2">
                                        <input
                                            v-model.number="ex.working_weight_kg"
                                            type="number"
                                            step="0.5"
                                            min="0"
                                            class="w-20 rounded border border-border bg-card px-2 py-1 font-mono tabular-nums"
                                        />
                                    </td>
                                    <td class="px-2 py-2">
                                        <input
                                            v-model.number="ex.prescribed_reps"
                                            type="number"
                                            min="1"
                                            class="w-16 rounded border border-border bg-card px-2 py-1 font-mono"
                                        />
                                    </td>
                                    <td class="px-2 py-2">
                                        <input
                                            v-if="ei === 0"
                                            v-model.number="block.working.set_count"
                                            type="number"
                                            min="1"
                                            class="w-14 rounded border border-border bg-card px-2 py-1 font-mono"
                                        />
                                    </td>
                                    <td class="px-2 py-2">
                                        <input
                                            v-if="ei === 0"
                                            v-model.number="block.working.rest_seconds"
                                            type="number"
                                            min="0"
                                            step="15"
                                            class="w-20 rounded border border-border bg-card px-2 py-1 font-mono"
                                        />
                                    </td>
                                    <td class="px-2 py-2">
                                        <input
                                            v-if="ei === 0"
                                            :value="warmUpText(block)"
                                            class="w-32 rounded border border-border bg-card px-2 py-1 font-mono text-primary/90"
                                            placeholder="40, 60, 80"
                                            @input="setWarmUpText(block, ($event.target as HTMLInputElement).value)"
                                        />
                                    </td>
                                    <td class="px-2 py-2">
                                        <div v-if="ei === 0" class="flex flex-col gap-1 text-xs">
                                            <label class="flex items-center gap-1">
                                                <input type="checkbox" :checked="block.is_superset" @change="toggleSuperset(block)" />
                                                SS
                                            </label>
                                            <label class="flex items-center gap-1">
                                                <input v-model="block.has_setup_after" type="checkbox" />
                                                Setup
                                            </label>
                                        </div>
                                    </td>
                                    <td class="px-2 py-2">
                                        <button
                                            v-if="ei === 0"
                                            type="button"
                                            class="text-xs text-muted-foreground hover:text-destructive"
                                            @click="removeBlock(bi)"
                                        >
                                            Remove
                                        </button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                    <p v-if="!form.blocks.length" class="px-4 py-8 text-center text-muted-foreground">No blocks yet. Add one below.</p>
                </div>
                <footer class="flex gap-2 border-t border-border px-4 py-3">
                    <button type="button" class="rounded border border-border px-3 py-2 text-sm hover:border-primary" @click="addBlock(false)">
                        + Block
                    </button>
                    <button type="button" class="rounded border border-border px-3 py-2 text-sm hover:border-primary" @click="addBlock(true)">
                        + Superset
                    </button>
                </footer>
            </div>

            <!-- Mobile: stage focus (B) -->
            <div class="flex flex-1 flex-col md:hidden">
                <div class="flex gap-2 overflow-x-auto px-4 py-3">
                    <button
                        v-for="(b, i) in form.blocks"
                        :key="i"
                        type="button"
                        class="shrink-0 rounded-lg border px-3 py-2 text-left text-sm"
                        :class="i === active ? 'border-primary bg-primary/10 text-primary' : 'border-border text-muted-foreground'"
                        @click="active = i"
                    >
                        <div class="font-mono text-xs">{{ i + 1 }}{{ b.is_superset ? ' SS' : '' }}</div>
                        <div class="max-w-28 truncate">{{ exerciseName(b.exercises[0]?.exercise_id) }}</div>
                    </button>
                    <button type="button" class="shrink-0 rounded-lg border border-dashed border-border px-4 text-muted-foreground" @click="addBlock(false)">
                        +
                    </button>
                </div>

                <main v-if="activeBlock" class="mx-auto flex w-full max-w-lg flex-1 flex-col gap-4 px-4 pb-28">
                    <div class="rounded-2xl border border-border bg-card p-5">
                        <div class="mb-4 flex items-center justify-between">
                            <h2 class="text-lg font-semibold">
                                Block {{ active + 1 }}
                                <span v-if="activeBlock.is_superset" class="ml-2 text-sm font-normal text-primary">Superset</span>
                            </h2>
                            <button type="button" class="text-xs text-destructive" @click="removeBlock(active)">Remove</button>
                        </div>

                        <div v-for="(ex, ei) in activeBlock.exercises" :key="ei" class="mb-6 last:mb-0">
                            <p v-if="activeBlock.is_superset" class="mb-1 font-mono text-xs text-muted-foreground">{{ ei === 0 ? 'A' : 'B' }}</p>
                            <select v-model.number="ex.exercise_id" class="w-full rounded-xl border border-border bg-background px-3 py-3 text-lg">
                                <option v-for="opt in exercises" :key="opt.id" :value="opt.id">{{ opt.name }}</option>
                            </select>
                            <div class="mt-3 grid grid-cols-2 gap-3">
                                <label class="block">
                                    <span class="text-xs text-muted-foreground">Working kg</span>
                                    <input
                                        v-model.number="ex.working_weight_kg"
                                        type="number"
                                        step="0.5"
                                        min="0"
                                        class="mt-1 w-full rounded-xl border border-border bg-background px-3 py-3 text-center text-3xl font-semibold tabular-nums outline-none focus:border-primary"
                                    />
                                </label>
                                <label class="block">
                                    <span class="text-xs text-muted-foreground">Target reps</span>
                                    <input
                                        v-model.number="ex.prescribed_reps"
                                        type="number"
                                        min="1"
                                        class="mt-1 w-full rounded-xl border border-border bg-background px-3 py-3 text-center text-3xl font-semibold tabular-nums outline-none focus:border-primary"
                                    />
                                </label>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3 border-t border-border pt-4">
                            <label>
                                <span class="text-xs text-muted-foreground">Working sets</span>
                                <input
                                    v-model.number="activeBlock.working.set_count"
                                    type="number"
                                    min="1"
                                    class="mt-1 w-full rounded-xl border border-border bg-background px-3 py-2 font-mono text-xl"
                                />
                            </label>
                            <label>
                                <span class="text-xs text-muted-foreground">Rest ({{ formatRest(activeBlock.working.rest_seconds) }})</span>
                                <input
                                    v-model.number="activeBlock.working.rest_seconds"
                                    type="number"
                                    min="0"
                                    step="15"
                                    class="mt-1 w-full rounded-xl border border-border bg-background px-3 py-2 font-mono text-xl"
                                />
                            </label>
                        </div>
                        <label class="mt-3 block">
                            <span class="text-xs text-muted-foreground">Warm-up % (comma-separated)</span>
                            <input
                                :value="warmUpText(activeBlock)"
                                class="mt-1 w-full rounded-xl border border-border bg-background px-3 py-2 font-mono text-primary/90"
                                @input="setWarmUpText(activeBlock, ($event.target as HTMLInputElement).value)"
                            />
                        </label>
                        <div class="mt-4 flex gap-4 text-sm">
                            <label class="flex items-center gap-2">
                                <input type="checkbox" :checked="activeBlock.is_superset" @change="toggleSuperset(activeBlock)" />
                                Superset
                            </label>
                            <label class="flex items-center gap-2">
                                <input v-model="activeBlock.has_setup_after" type="checkbox" />
                                Setup after
                            </label>
                        </div>
                    </div>
                </main>

                <p v-else class="px-4 py-12 text-center text-muted-foreground">No blocks. Tap + to add.</p>

                <div class="fixed right-0 bottom-4 left-0 flex justify-center gap-3 px-4">
                    <button type="button" class="rounded-full bg-secondary px-5 py-3 text-sm" @click="addBlock(true)">+ SS</button>
                    <button
                        type="button"
                        class="rounded-full bg-primary px-5 py-3 text-sm font-semibold text-primary-foreground disabled:opacity-50"
                        :disabled="form.processing"
                        @click="save"
                    >
                        Save
                    </button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
