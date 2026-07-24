<script setup lang="ts">
/**
 * Workout player — phone-first stage (dark zinc + lime).
 */
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';

type PlayerSet = {
    id: number;
    workout_block_exercise_id: number;
    exercise_name: string;
    set_index: number;
    group_type: 'warm_up' | 'working';
    target_weight_kg: number | null;
    target_reps: number | null;
    logged_weight_kg: number | null;
    logged_reps: number | null;
    completed: boolean;
    rest_seconds: number;
};

type PlayerBlock = {
    id: number;
    position: number;
    is_superset: boolean;
    has_setup_after: boolean;
    exercises: Array<{
        id: number;
        name: string;
        working_weight_kg: number;
        prescribed_reps: number;
        position: number;
    }>;
    sets: PlayerSet[];
};

type WorkoutPayload = {
    id: number;
    routine_name: string;
    mode: string;
    status: string;
    weight_unit: string;
    blocks: PlayerBlock[];
};

const props = defineProps<{ workout: WorkoutPayload }>();

type Focus =
    | { kind: 'set'; blockIndex: number; setId: number }
    | { kind: 'setup'; blockIndex: number }
    | { kind: 'done' };

const flatSets = computed(() =>
    props.workout.blocks.flatMap((block, blockIndex) =>
        block.sets.map((set) => ({ blockIndex, block, set })),
    ),
);

const firstIncomplete = (): Focus => {
    for (let blockIndex = 0; blockIndex < props.workout.blocks.length; blockIndex++) {
        const block = props.workout.blocks[blockIndex];
        const incomplete = block.sets.find((set) => !set.completed);
        if (incomplete) {
            return { kind: 'set', blockIndex, setId: incomplete.id };
        }
        if (block.has_setup_after && !setupDone.value[block.id]) {
            return { kind: 'setup', blockIndex };
        }
    }
    return { kind: 'done' };
};

const setupDone = ref<Record<number, boolean>>({});
const focus = ref<Focus>(firstIncomplete());

watch(
    () => props.workout,
    () => {
        if (restSecondsLeft.value > 0) {
            return;
        }
        focus.value = firstIncomplete();
    },
    { deep: true },
);

const current = computed(() => {
    if (focus.value.kind !== 'set') return null;
    return flatSets.value.find(({ set }) => set.id === (focus.value as { setId: number }).setId) ?? null;
});

const currentBlock = computed(() => {
    if (focus.value.kind === 'done') return null;
    return props.workout.blocks[focus.value.blockIndex] ?? null;
});

const setForm = useForm({
    reps: 0,
    weight_kg: 0,
});

watch(
    current,
    (entry) => {
        if (!entry) return;
        setForm.reps = entry.set.logged_reps ?? entry.set.target_reps ?? 0;
        setForm.weight_kg = entry.set.logged_weight_kg ?? entry.set.target_weight_kg ?? 0;
    },
    { immediate: true },
);

const progressLabel = computed(() => {
    const total = flatSets.value.length;
    const done = flatSets.value.filter(({ set }) => set.completed).length;
    return `${done}/${total}`;
});

const restSecondsLeft = ref(0);
const restLabel = computed(() => {
    const s = restSecondsLeft.value;
    const m = Math.floor(s / 60);
    const r = s % 60;
    return m > 0 ? `${m}:${r.toString().padStart(2, '0')}` : `${r}s`;
});

let restTimer: ReturnType<typeof setInterval> | null = null;

const clearRest = () => {
    if (restTimer) {
        clearInterval(restTimer);
        restTimer = null;
    }
    restSecondsLeft.value = 0;
};

const startRest = (seconds: number) => {
    clearRest();
    if (seconds <= 0) {
        focus.value = firstIncomplete();
        return;
    }
    restSecondsLeft.value = seconds;
    restTimer = setInterval(() => {
        restSecondsLeft.value -= 1;
        if (restSecondsLeft.value <= 0) {
            clearRest();
            focus.value = firstIncomplete();
        }
    }, 1000);
};

onBeforeUnmount(clearRest);

onMounted(() => {
    focus.value = firstIncomplete();
});

const shouldRestAfter = (block: PlayerBlock, set: PlayerSet): boolean => {
    if (!block.is_superset) {
        return true;
    }
    const sameIndex = block.sets.filter((s) => s.set_index === set.set_index && s.group_type === set.group_type);
    return sameIndex.every((s) => s.completed || s.id === set.id);
};

const completeSet = () => {
    if (!current.value || props.workout.status !== 'in_progress') return;
    const { block, set } = current.value;
    const restAfter = shouldRestAfter(block, set) ? set.rest_seconds : 0;

    setForm.post(route('workouts.sets.complete', { workout: props.workout.id, set: set.id }), {
        preserveScroll: true,
        onSuccess: () => {
            if (restAfter > 0) {
                startRest(restAfter);
            } else {
                focus.value = firstIncomplete();
            }
        },
    });
};

const skipRest = () => {
    clearRest();
    focus.value = firstIncomplete();
};

const acknowledgeSetup = () => {
    if (focus.value.kind !== 'setup') return;
    const block = props.workout.blocks[focus.value.blockIndex];
    setupDone.value[block.id] = true;
    focus.value = firstIncomplete();
};

const finishWorkout = () => {
    router.post(route('workouts.finish', props.workout.id));
};

const groupLabel = (type: string) => (type === 'warm_up' ? 'Warm-up' : 'Working');
</script>

<template>
    <Head :title="`Play · ${workout.routine_name}`" />

    <AppLayout>
        <div class="mx-auto flex min-h-[calc(100vh-4rem)] w-full max-w-lg flex-col bg-zinc-950 text-zinc-100">
            <header class="flex items-center justify-between border-b border-zinc-800 px-4 py-3">
                <div>
                    <p class="text-xs uppercase tracking-wide text-zinc-500">{{ workout.mode }}</p>
                    <h1 class="text-lg font-semibold">{{ workout.routine_name }}</h1>
                </div>
                <div class="text-right font-mono text-sm text-zinc-400">{{ progressLabel }}</div>
            </header>

            <div v-if="restSecondsLeft > 0" class="flex flex-1 flex-col items-center justify-center gap-4 px-6">
                <p class="text-sm uppercase tracking-widest text-zinc-500">Rest</p>
                <p class="font-mono text-6xl font-semibold text-lime-400">{{ restLabel }}</p>
                <button type="button" class="rounded-full border border-zinc-700 px-5 py-2 text-sm" @click="skipRest">Skip</button>
            </div>

            <div v-else-if="focus.kind === 'setup' && currentBlock" class="flex flex-1 flex-col items-center justify-center gap-6 px-6">
                <p class="text-sm uppercase tracking-widest text-zinc-500">Setup</p>
                <p class="text-center text-2xl font-semibold">Change equipment, then continue</p>
                <p class="text-sm text-zinc-500">After block {{ currentBlock.position }}</p>
                <button type="button" class="rounded-full bg-lime-400 px-8 py-3 text-sm font-semibold text-zinc-950" @click="acknowledgeSetup">
                    Setup done
                </button>
            </div>

            <div v-else-if="focus.kind === 'done'" class="flex flex-1 flex-col items-center justify-center gap-6 px-6">
                <p class="text-sm uppercase tracking-widest text-zinc-500">Complete</p>
                <p class="text-center text-2xl font-semibold">All sets logged</p>
                <button
                    type="button"
                    class="rounded-full bg-lime-400 px-8 py-3 text-sm font-semibold text-zinc-950"
                    :disabled="workout.status !== 'in_progress'"
                    @click="finishWorkout"
                >
                    Finish workout
                </button>
            </div>

            <div v-else-if="current" class="flex flex-1 flex-col px-4 py-6">
                <p class="text-xs uppercase tracking-widest text-zinc-500">
                    Block {{ current.block.position }} · {{ groupLabel(current.set.group_type) }} · Set
                    {{ current.set.set_index + 1 }}
                    <span v-if="current.block.is_superset"> · Superset</span>
                </p>
                <h2 class="mt-2 text-3xl font-semibold leading-tight">{{ current.set.exercise_name }}</h2>
                <p class="mt-2 font-mono text-zinc-400">
                    Target
                    <span v-if="current.set.target_weight_kg != null">{{ current.set.target_weight_kg }}{{ workout.weight_unit }}</span>
                    <span v-if="current.set.target_reps != null"> × {{ current.set.target_reps }}</span>
                    <span v-else> warm-up</span>
                </p>

                <form class="mt-8 flex flex-1 flex-col gap-4" @submit.prevent="completeSet">
                    <label class="flex flex-col gap-1 text-sm text-zinc-400">
                        Weight ({{ workout.weight_unit }})
                        <input
                            v-model.number="setForm.weight_kg"
                            type="number"
                            step="0.5"
                            min="0"
                            class="rounded-xl border border-zinc-700 bg-zinc-900 px-4 py-3 text-lg text-zinc-100"
                            required
                        />
                    </label>
                    <label class="flex flex-col gap-1 text-sm text-zinc-400">
                        Reps
                        <input
                            v-model.number="setForm.reps"
                            type="number"
                            min="0"
                            max="100"
                            class="rounded-xl border border-zinc-700 bg-zinc-900 px-4 py-3 text-lg text-zinc-100"
                            required
                        />
                    </label>
                    <div class="mt-auto flex flex-col gap-3 pb-4">
                        <button
                            type="submit"
                            class="rounded-full bg-lime-400 px-6 py-4 text-base font-semibold text-zinc-950 disabled:opacity-50"
                            :disabled="setForm.processing || workout.status !== 'in_progress'"
                        >
                            Complete set
                        </button>
                        <button
                            v-if="flatSets.every(({ set }) => set.completed)"
                            type="button"
                            class="rounded-full border border-zinc-700 px-6 py-3 text-sm"
                            @click="finishWorkout"
                        >
                            Finish workout
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
