<script setup lang="ts">
/**
 * Routine editor — desktop: dense list (A), mobile: stage (B), styling: B (dark zinc + lime).
 */
import InputError from '@/components/InputError.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ChevronDown } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';

type ExerciseOption = { id: number; name: string; primary_muscle_group: string };

type BlockExercise = {
    exercise_id: number | null;
    working_weight_kg: number;
    prescribed_reps: number;
    achievement_floor: number | null;
    progression_target: number | null;
};

type WarmUpStep = { percent: number; reps: number };

type Block = {
    is_superset: boolean;
    has_setup_after: boolean;
    has_setup_after_warm_up: boolean;
    exercises: BlockExercise[];
    working: { set_count: number; rest_seconds: number };
    warm_up: { set_count: number; rest_seconds: number; steps: WarmUpStep[] };
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
    warm_up_defaults: WarmUpStep[];
}>();

const exerciseQuery = ref('');

const filteredExercises = computed(() => {
    const q = exerciseQuery.value.trim().toLowerCase();
    if (!q) {
        return props.exercises;
    }
    return props.exercises.filter(
        (e) =>
            e.name.toLowerCase().includes(q) ||
            (e.primary_muscle_group ?? '').toLowerCase().includes(q),
    );
});

const findMatches = computed(() => {
    const q = exerciseQuery.value.trim();
    if (!q) {
        return [];
    }
    return filteredExercises.value.slice(0, 40);
});

/** Keep the current selection visible even when it falls outside the filter. */
const exerciseOptionsFor = (selectedId: number | null) => {
    const list = filteredExercises.value;
    if (selectedId == null || list.some((e) => e.id === selectedId)) {
        return list;
    }
    const selected = props.exercises.find((e) => e.id === selectedId);
    return selected ? [selected, ...list] : list;
};

const emptyExercise = (): BlockExercise => ({
    exercise_id: props.exercises[0]?.id ?? null,
    working_weight_kg: 60,
    prescribed_reps: 6,
    achievement_floor: null,
    progression_target: null,
});

const defaultWarmUpSteps = (): WarmUpStep[] =>
    (props.warm_up_defaults?.length ? props.warm_up_defaults : []).map((s) => ({
        percent: s.percent,
        reps: s.reps,
    }));

const emptyBlock = (superset = false): Block => {
    const steps = defaultWarmUpSteps();
    return {
        is_superset: superset,
        has_setup_after: false,
        has_setup_after_warm_up: false,
        exercises: superset ? [emptyExercise(), emptyExercise()] : [emptyExercise()],
        working: { set_count: 3, rest_seconds: 120 },
        warm_up: { set_count: steps.length, rest_seconds: 60, steps },
    };
};

const normalizeBlock = (raw: Block): Block => {
    const steps = (raw.warm_up?.steps ?? []).map((s) => ({
        percent: Number(s.percent),
        reps: Number(s.reps ?? 5),
    }));
    return {
        ...raw,
        has_setup_after_warm_up: Boolean(raw.has_setup_after_warm_up),
        warm_up: {
            set_count: raw.warm_up?.set_count ?? steps.length,
            rest_seconds: raw.warm_up?.rest_seconds ?? 60,
            steps,
        },
    };
};

const form = useForm({
    name: props.routine.name,
    deload_weight_factor: props.routine.deload_weight_factor,
    deload_reps_factor: props.routine.deload_reps_factor,
    // Inertia props are nested reactive proxies — structuredClone cannot clone them
    blocks: props.routine.blocks.length
        ? (JSON.parse(JSON.stringify(props.routine.blocks)) as Block[]).map(normalizeBlock)
        : ([] as Block[]),
});

const active = ref(0);
const activeExerciseIndex = ref(0);
const warmUpExpanded = ref(false);
watch(
    () => form.blocks.length,
    (len) => {
        if (active.value >= len) {
            active.value = Math.max(0, len - 1);
        }
        activeExerciseIndex.value = 0;
    },
);
watch(active, () => {
    warmUpExpanded.value = false;
    exerciseQuery.value = '';
    activeExerciseIndex.value = 0;
});

const activeBlock = computed(() => form.blocks[active.value] ?? null);

const selectBlockExercise = (blockIndex: number, exerciseIndex = 0) => {
    active.value = blockIndex;
    activeExerciseIndex.value = exerciseIndex;
};

const applyExercisePick = (exerciseId: number) => {
    const block = form.blocks[active.value];
    const exercise = block?.exercises[activeExerciseIndex.value] ?? block?.exercises[0];
    if (!exercise) {
        return;
    }
    exercise.exercise_id = exerciseId;
    exerciseQuery.value = '';
};

/** Compact editor string: `40x5, 60x3, 80x1` (also accepts legacy `40, 60, 80`). */
const warmUpText = (block: Block) =>
    block.warm_up.steps.map((s) => `${s.percent}x${s.reps}`).join(', ');

const setWarmUpText = (block: Block, value: string) => {
    block.warm_up.steps = value
        .split(',')
        .map((part) => part.trim())
        .filter(Boolean)
        .map((part) => {
            const withReps = part.match(/^(\d+)\s*[x×]\s*(\d+)$/i);
            if (withReps) {
                return { percent: parseInt(withReps[1], 10), reps: parseInt(withReps[2], 10) };
            }
            const percentOnly = parseInt(part, 10);
            if (!Number.isNaN(percentOnly) && percentOnly > 0) {
                return { percent: percentOnly, reps: 5 };
            }
            return null;
        })
        .filter((s): s is WarmUpStep => s !== null && s.percent > 0 && s.reps > 0);
    block.warm_up.set_count = block.warm_up.steps.length;
};

const addWarmUpStep = (block: Block) => {
    block.warm_up.steps.push({ percent: 50, reps: 5 });
    block.warm_up.set_count = block.warm_up.steps.length;
};

const removeWarmUpStep = (block: Block, index: number) => {
    block.warm_up.steps.splice(index, 1);
    block.warm_up.set_count = block.warm_up.steps.length;
};

const clearWarmUp = (block: Block) => {
    block.warm_up.steps = [];
    block.warm_up.set_count = 0;
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
    form.put(route('routines.update', props.routine.id));
};

const deleteRoutine = () => {
    if (!confirm(`Delete “${form.name || 'this routine'}”? It will be archived and removed from your list.`)) {
        return;
    }
    router.delete(route('routines.delete', props.routine.id));
};

const errorList = computed(() => Object.values(form.errors));
</script>

<template>
    <AppLayout :breadcrumbs="[{ title: 'Dashboard', href: '/dashboard' }, { title: form.name || 'Routine', href: '#' }]">
        <Head :title="`Edit · ${form.name}`" />

        <div class="flex flex-1 flex-col overscroll-y-none bg-background text-foreground">
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
                        <Link
                            :href="route('dashboard')"
                            class="rounded-full border border-border px-4 py-2 text-sm font-medium text-muted-foreground transition-colors hover:text-foreground"
                        >
                            Cancel
                        </Link>
                        <button
                            type="button"
                            class="rounded-full border border-destructive/50 px-4 py-2 text-sm font-medium text-destructive transition-colors hover:bg-destructive/10"
                            @click="deleteRoutine"
                        >
                            Delete
                        </button>
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

            <div class="hidden border-b border-border px-4 py-3 md:block md:px-6">
                <label class="flex flex-col gap-1 text-xs text-muted-foreground">
                    Find exercise
                    <input
                        v-model="exerciseQuery"
                        type="search"
                        placeholder="Name or muscle group…"
                        class="w-full max-w-md rounded-xl border border-border bg-card px-3 py-2 text-sm text-foreground outline-none focus:border-primary"
                    />
                </label>
                <p class="mt-1 text-xs text-muted-foreground">
                    Showing {{ filteredExercises.length }} of {{ exercises.length }}
                    <span v-if="exerciseQuery.trim() && activeBlock"> · tap to set selected exercise</span>
                </p>
                <ul
                    v-if="findMatches.length"
                    class="mt-2 max-h-48 max-w-md overflow-y-auto divide-y divide-border rounded-xl border border-border"
                >
                    <li v-for="exercise in findMatches" :key="exercise.id">
                        <button
                            type="button"
                            class="flex w-full flex-col items-start gap-0.5 px-3 py-2 text-left hover:bg-secondary"
                            :disabled="!activeBlock"
                            @click="applyExercisePick(exercise.id)"
                        >
                            <span class="text-sm font-medium text-foreground">{{ exercise.name }}</span>
                            <span class="font-mono text-xs text-muted-foreground">{{
                                exercise.primary_muscle_group
                            }}</span>
                        </button>
                    </li>
                </ul>
                <p
                    v-else-if="exerciseQuery.trim()"
                    class="mt-2 text-xs text-muted-foreground"
                >
                    No matches.
                </p>
            </div>

            <!-- Desktop: dense list (A structure) -->
            <div class="hidden flex-1 flex-col md:flex">
                <div class="flex-1 overflow-x-auto px-2 py-3">
                    <table class="w-full min-w-[60rem] border-collapse text-left text-sm">
                        <thead>
                            <tr class="border-b border-border font-mono text-xs uppercase text-muted-foreground">
                                <th class="px-2 py-2">#</th>
                                <th class="px-2 py-2">Exercise</th>
                                <th class="px-2 py-2">kg</th>
                                <th class="px-2 py-2">Reps</th>
                                <th class="px-2 py-2">Sets</th>
                                <th class="px-2 py-2">Rest</th>
                                <th class="px-2 py-2">Warm-up %×reps</th>
                                <th class="px-2 py-2">WU rest</th>
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
                                    :class="bi === active && ei === activeExerciseIndex ? 'bg-primary/5' : ''"
                                    @click="selectBlockExercise(bi, ei)"
                                >
                                    <td class="px-2 py-2 font-mono text-muted-foreground">{{ ei === 0 ? bi + 1 : '' }}</td>
                                    <td class="px-2 py-2">
                                        <div class="flex items-center gap-2">
                                            <span v-if="block.is_superset" class="font-mono text-xs text-primary">{{ ei === 0 ? 'A' : 'B' }}</span>
                                            <select
                                                v-model.number="ex.exercise_id"
                                                class="max-w-xs rounded border border-border bg-card px-2 py-1"
                                                @focus="selectBlockExercise(bi, ei)"
                                            >
                                                <option
                                                    v-for="opt in exerciseOptionsFor(ex.exercise_id)"
                                                    :key="opt.id"
                                                    :value="opt.id"
                                                >
                                                    {{ opt.name }}
                                                </option>
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
                                        <div v-if="ei === 0" class="flex items-center gap-1">
                                            <input
                                                :value="warmUpText(block)"
                                                class="w-32 rounded border border-border bg-card px-2 py-1 font-mono text-primary/90"
                                                placeholder="40x5, 60x3, 80x1"
                                                @input="setWarmUpText(block, ($event.target as HTMLInputElement).value)"
                                            />
                                            <button
                                                v-if="block.warm_up.steps.length"
                                                type="button"
                                                class="shrink-0 text-xs text-muted-foreground hover:text-destructive"
                                                title="Clear warm-up"
                                                @click="clearWarmUp(block)"
                                            >
                                                Clear
                                            </button>
                                        </div>
                                    </td>
                                    <td class="px-2 py-2">
                                        <input
                                            v-if="ei === 0"
                                            v-model.number="block.warm_up.rest_seconds"
                                            type="number"
                                            min="0"
                                            step="15"
                                            class="w-20 rounded border border-border bg-card px-2 py-1 font-mono"
                                        />
                                    </td>
                                    <td class="px-2 py-2">
                                        <div v-if="ei === 0" class="flex flex-col gap-1 text-xs">
                                            <label class="flex items-center gap-1">
                                                <input type="checkbox" :checked="block.is_superset" @change="toggleSuperset(block)" />
                                                SS
                                            </label>
                                            <label class="flex items-center gap-1">
                                                <input v-model="block.has_setup_after_warm_up" type="checkbox" />
                                                Setup→work
                                            </label>
                                            <label class="flex items-center gap-1">
                                                <input v-model="block.has_setup_after" type="checkbox" />
                                                Setup→next
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
            <div class="flex flex-col md:hidden">
                <div class="flex gap-2 overflow-x-auto px-4 py-3">
                    <button
                        v-for="(b, i) in form.blocks"
                        :key="i"
                        type="button"
                        class="shrink-0 rounded-lg border px-3 py-2 text-left text-sm"
                        :class="i === active ? 'border-primary bg-primary/10 text-primary' : 'border-border text-muted-foreground'"
                        @click="selectBlockExercise(i, 0)"
                    >
                        <div class="font-mono text-xs">{{ i + 1 }}{{ b.is_superset ? ' SS' : '' }}</div>
                        <div class="max-w-28 truncate">{{ exerciseName(b.exercises[0]?.exercise_id) }}</div>
                    </button>
                </div>

                <main v-if="activeBlock" class="mx-auto flex w-full max-w-lg flex-col gap-4 px-4 pb-28">
                    <div class="rounded-2xl border border-border bg-card p-4">
                        <div class="mb-3 flex items-center justify-between">
                            <h2 class="text-base font-semibold">
                                Block {{ active + 1 }}
                                <span v-if="activeBlock.is_superset" class="ml-2 text-sm font-normal text-primary">Superset</span>
                            </h2>
                            <button type="button" class="text-xs text-destructive" @click="removeBlock(active)">Remove</button>
                        </div>

                        <div class="mb-4">
                            <label class="flex flex-col gap-1 text-xs text-muted-foreground">
                                Find exercise
                                <input
                                    v-model="exerciseQuery"
                                    type="search"
                                    placeholder="Name or muscle group…"
                                    class="w-full rounded-xl border border-border bg-background px-3 py-2.5 text-base text-foreground outline-none focus:border-primary"
                                />
                            </label>
                            <p v-if="activeBlock.is_superset" class="mt-1 text-xs text-muted-foreground">
                                Sets {{ activeExerciseIndex === 0 ? 'A' : 'B' }} · tap a match or focus a slot below
                            </p>
                            <ul
                                v-if="findMatches.length"
                                class="mt-2 max-h-40 overflow-y-auto divide-y divide-border rounded-xl border border-border"
                            >
                                <li v-for="exercise in findMatches" :key="exercise.id">
                                    <button
                                        type="button"
                                        class="flex w-full flex-col items-start gap-0.5 px-3 py-2.5 text-left active:bg-secondary"
                                        @click="applyExercisePick(exercise.id)"
                                    >
                                        <span class="text-sm font-medium text-foreground">{{ exercise.name }}</span>
                                        <span class="font-mono text-xs text-muted-foreground">{{
                                            exercise.primary_muscle_group
                                        }}</span>
                                    </button>
                                </li>
                            </ul>
                            <p v-else-if="exerciseQuery.trim()" class="mt-2 text-xs text-muted-foreground">No matches.</p>
                        </div>

                        <div v-for="(ex, ei) in activeBlock.exercises" :key="ei" class="mb-4 last:mb-0">
                            <p v-if="activeBlock.is_superset" class="mb-1 font-mono text-xs text-muted-foreground">{{ ei === 0 ? 'A' : 'B' }}</p>
                            <select
                                v-model.number="ex.exercise_id"
                                class="w-full rounded-xl border border-border bg-background px-3 py-2.5 text-base"
                                :class="ei === activeExerciseIndex ? 'border-primary' : ''"
                                @focus="selectBlockExercise(active, ei)"
                            >
                                <option
                                    v-for="opt in exerciseOptionsFor(ex.exercise_id)"
                                    :key="opt.id"
                                    :value="opt.id"
                                >
                                    {{ opt.name }}
                                </option>
                            </select>
                            <div class="mt-2 grid grid-cols-2 gap-2">
                                <label class="block">
                                    <span class="text-xs text-muted-foreground">Working kg</span>
                                    <input
                                        v-model.number="ex.working_weight_kg"
                                        type="number"
                                        step="0.5"
                                        min="0"
                                        class="mt-1 w-full rounded-xl border border-border bg-background px-3 py-2 text-center text-2xl font-semibold tabular-nums outline-none focus:border-primary"
                                    />
                                </label>
                                <label class="block">
                                    <span class="text-xs text-muted-foreground">Target reps</span>
                                    <input
                                        v-model.number="ex.prescribed_reps"
                                        type="number"
                                        min="1"
                                        class="mt-1 w-full rounded-xl border border-border bg-background px-3 py-2 text-center text-2xl font-semibold tabular-nums outline-none focus:border-primary"
                                    />
                                </label>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-2 border-t border-border pt-3">
                            <label>
                                <span class="text-xs text-muted-foreground">Working sets</span>
                                <input
                                    v-model.number="activeBlock.working.set_count"
                                    type="number"
                                    min="1"
                                    class="mt-1 w-full rounded-xl border border-border bg-background px-3 py-2 font-mono text-lg"
                                />
                            </label>
                            <label>
                                <span class="text-xs text-muted-foreground">Rest ({{ formatRest(activeBlock.working.rest_seconds) }})</span>
                                <input
                                    v-model.number="activeBlock.working.rest_seconds"
                                    type="number"
                                    min="0"
                                    step="15"
                                    class="mt-1 w-full rounded-xl border border-border bg-background px-3 py-2 font-mono text-lg"
                                />
                            </label>
                        </div>

                        <div class="mt-3 border-t border-border pt-3">
                            <button
                                type="button"
                                class="flex w-full items-center justify-between gap-2 text-left"
                                :aria-expanded="warmUpExpanded"
                                @click="warmUpExpanded = !warmUpExpanded"
                            >
                                <span class="min-w-0">
                                    <span class="block text-xs text-muted-foreground">Warm-up</span>
                                    <span class="block truncate font-mono text-sm text-foreground">
                                        {{
                                            activeBlock.warm_up.steps.length
                                                ? warmUpText(activeBlock)
                                                : 'None'
                                        }}
                                    </span>
                                </span>
                                <ChevronDown
                                    class="size-4 shrink-0 text-muted-foreground transition-transform"
                                    :class="warmUpExpanded ? 'rotate-180' : ''"
                                />
                            </button>
                            <div v-if="warmUpExpanded" class="mt-3 space-y-2">
                                <label class="block">
                                    <span class="text-xs text-muted-foreground">Compact (40x5, 60x3)</span>
                                    <input
                                        :value="warmUpText(activeBlock)"
                                        class="mt-1 w-full rounded-xl border border-border bg-background px-3 py-2 font-mono text-sm text-primary/90"
                                        @change="setWarmUpText(activeBlock, ($event.target as HTMLInputElement).value)"
                                    />
                                </label>
                                <label class="block">
                                    <span class="text-xs text-muted-foreground"
                                        >Warm-up rest ({{ formatRest(activeBlock.warm_up.rest_seconds) }})</span
                                    >
                                    <input
                                        v-model.number="activeBlock.warm_up.rest_seconds"
                                        type="number"
                                        min="0"
                                        step="15"
                                        class="mt-1 w-full rounded-xl border border-border bg-background px-3 py-2 font-mono text-lg"
                                    />
                                </label>
                                <div
                                    v-for="(step, si) in activeBlock.warm_up.steps"
                                    :key="si"
                                    class="flex items-center gap-1.5"
                                >
                                    <input
                                        v-model.number="step.percent"
                                        type="number"
                                        min="1"
                                        max="100"
                                        class="w-16 rounded-lg border border-border bg-background px-2 py-1.5 font-mono text-sm"
                                        aria-label="Warm-up percent"
                                    />
                                    <span class="text-xs text-muted-foreground">×</span>
                                    <input
                                        v-model.number="step.reps"
                                        type="number"
                                        min="1"
                                        max="100"
                                        class="w-14 rounded-lg border border-border bg-background px-2 py-1.5 font-mono text-sm"
                                        aria-label="Warm-up reps"
                                    />
                                    <button
                                        type="button"
                                        class="ml-auto text-xs text-muted-foreground hover:text-destructive"
                                        @click="removeWarmUpStep(activeBlock, si)"
                                    >
                                        −
                                    </button>
                                </div>
                                <div class="flex items-center gap-3">
                                    <button
                                        type="button"
                                        class="text-xs text-primary"
                                        @click="addWarmUpStep(activeBlock)"
                                    >
                                        + Step
                                    </button>
                                    <button
                                        v-if="activeBlock.warm_up.steps.length"
                                        type="button"
                                        class="text-xs text-muted-foreground hover:text-destructive"
                                        @click="clearWarmUp(activeBlock)"
                                    >
                                        Clear warm-up
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="mt-3 flex flex-wrap gap-4 border-t border-border pt-3 text-sm">
                            <label class="flex items-center gap-2">
                                <input type="checkbox" :checked="activeBlock.is_superset" @change="toggleSuperset(activeBlock)" />
                                Superset
                            </label>
                            <label class="flex items-center gap-2">
                                <input v-model="activeBlock.has_setup_after_warm_up" type="checkbox" />
                                Setup before working
                            </label>
                            <label class="flex items-center gap-2">
                                <input v-model="activeBlock.has_setup_after" type="checkbox" />
                                Setup after block
                            </label>
                        </div>
                    </div>

                    <div class="flex gap-2">
                        <button
                            type="button"
                            class="flex-1 rounded-xl border border-dashed border-border px-4 py-3 text-sm text-muted-foreground hover:border-primary hover:text-primary"
                            @click="addBlock(false)"
                        >
                            Add block
                        </button>
                        <button
                            type="button"
                            class="flex-1 rounded-xl border border-dashed border-border px-4 py-3 text-sm text-muted-foreground hover:border-primary hover:text-primary"
                            @click="addBlock(true)"
                        >
                            Add superset
                        </button>
                    </div>
                </main>

                <div v-else class="px-4 pb-28">
                    <p class="py-8 text-center text-muted-foreground">No blocks yet.</p>
                    <div class="flex gap-2">
                        <button
                            type="button"
                            class="flex-1 rounded-xl border border-dashed border-border px-4 py-3 text-sm text-muted-foreground hover:border-primary hover:text-primary"
                            @click="addBlock(false)"
                        >
                            Add block
                        </button>
                        <button
                            type="button"
                            class="flex-1 rounded-xl border border-dashed border-border px-4 py-3 text-sm text-muted-foreground hover:border-primary hover:text-primary"
                            @click="addBlock(true)"
                        >
                            Add superset
                        </button>
                    </div>
                </div>

                <div
                    class="fixed right-0 bottom-0 left-0 flex justify-center gap-2 px-4 pt-2 pb-[max(1rem,env(safe-area-inset-bottom,0px))]"
                >
                    <Link
                        :href="route('dashboard')"
                        class="rounded-full border border-border bg-background px-4 py-3 text-sm text-muted-foreground"
                    >
                        Cancel
                    </Link>
                    <button
                        type="button"
                        class="rounded-full border border-destructive/50 bg-background px-4 py-3 text-sm text-destructive"
                        @click="deleteRoutine"
                    >
                        Delete
                    </button>
                    <button
                        type="button"
                        class="rounded-full bg-primary px-4 py-3 text-sm font-semibold text-primary-foreground disabled:opacity-50"
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
