<script setup lang="ts">
/**
 * Workout player — chrome-minimal full-bleed stage.
 */
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

const previousSetWeightKg = (entry: { block: PlayerBlock; set: PlayerSet }): number | null => {
    const prior = entry.block.sets
        .filter(
            (s) =>
                s.workout_block_exercise_id === entry.set.workout_block_exercise_id &&
                s.group_type === entry.set.group_type &&
                s.set_index < entry.set.set_index &&
                s.completed &&
                s.logged_weight_kg != null,
        )
        .sort((a, b) => b.set_index - a.set_index)[0];

    return prior?.logged_weight_kg ?? null;
};

watch(
    current,
    (entry) => {
        if (!entry) return;
        setForm.reps = entry.set.logged_reps ?? entry.set.target_reps ?? 0;
        setForm.weight_kg =
            entry.set.logged_weight_kg ?? previousSetWeightKg(entry) ?? entry.set.target_weight_kg ?? 0;
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

const leaveConfirmed = ref(false);

const onBeforeUnload = (event: BeforeUnloadEvent) => {
    if (props.workout.status !== 'in_progress') return;
    event.preventDefault();
    event.returnValue = '';
};

const visitLeavesWorkout = (visit: { url: string | URL }): boolean => {
    const url = typeof visit.url === 'string' ? new URL(visit.url, window.location.origin) : visit.url;
    return !url.pathname.startsWith(`/workouts/${props.workout.id}`);
};

let removeBeforeListener: (() => void) | undefined;

onBeforeUnmount(() => {
    clearRest();
    removeBeforeListener?.();
    window.removeEventListener('beforeunload', onBeforeUnload);
});

onMounted(() => {
    focus.value = firstIncomplete();
    window.addEventListener('beforeunload', onBeforeUnload);
    removeBeforeListener = router.on('before', (event) => {
        if (leaveConfirmed.value) return;
        if (props.workout.status !== 'in_progress') return;
        if (!visitLeavesWorkout(event.detail.visit)) return;
        if (!confirm('Leave workout? Progress is saved — you can resume from the dashboard.')) {
            event.preventDefault();
        }
    });
});

const leaveWorkout = () => {
    if (props.workout.status === 'in_progress') {
        if (!confirm('Leave workout? Progress is saved — you can resume from the dashboard.')) {
            return;
        }
    }
    leaveConfirmed.value = true;
    router.visit(route('dashboard'));
};

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
    leaveConfirmed.value = true;
    router.post(
        route('workouts.finish', props.workout.id),
        {},
        {
            onError: () => {
                leaveConfirmed.value = false;
            },
        },
    );
};

const workingRoundsInBlock = computed(() => {
    if (!current.value) return 0;
    const indexes = new Set(
        current.value.block.sets.filter((s) => s.group_type === 'working').map((s) => s.set_index),
    );
    return indexes.size;
});

const canAddWorkingSet = computed(
    () =>
        props.workout.status === 'in_progress' &&
        current.value !== null &&
        current.value.set.group_type === 'working',
);

const canRemoveWorkingSet = computed(() => {
    if (!current.value || props.workout.status !== 'in_progress') return false;
    if (current.value.set.group_type !== 'working' || current.value.set.completed) return false;
    if (workingRoundsInBlock.value <= 1) return false;

    const index = current.value.set.set_index;
    const round = current.value.block.sets.filter(
        (s) => s.group_type === 'working' && s.set_index === index,
    );
    return round.every((s) => !s.completed);
});

const addWorkingSet = () => {
    if (!current.value) return;
    router.post(route('workouts.working-sets.add', [props.workout.id, current.value.block.id]));
};

const removeWorkingSet = () => {
    if (!current.value) return;
    router.delete(route('workouts.sets.remove', [props.workout.id, current.value.set.id]));
};

const groupLabel = (type: string) => (type === 'warm_up' ? 'Warm-up' : 'Working');
</script>

<template>
    <Head :title="`Play · ${workout.routine_name}`" />

    <div
        class="mx-auto flex min-h-dvh w-full max-w-lg flex-col overscroll-none bg-background text-foreground safe-pt safe-pb safe-px"
    >
        <header class="flex items-center justify-between border-b border-border px-4 py-3">
            <div class="min-w-0">
                <p class="text-xs uppercase tracking-wide text-muted-foreground">{{ workout.mode }}</p>
                <h1 class="truncate text-lg font-semibold">{{ workout.routine_name }}</h1>
            </div>
            <div class="flex shrink-0 items-center gap-3">
                <div class="font-mono text-sm text-muted-foreground">{{ progressLabel }}</div>
                <button
                    type="button"
                    class="rounded-md border border-border px-3 py-1.5 text-sm text-muted-foreground hover:text-foreground"
                    @click="leaveWorkout"
                >
                    Leave
                </button>
            </div>
        </header>

        <div v-if="restSecondsLeft > 0" class="flex flex-1 flex-col items-center justify-center gap-4 px-6">
            <p class="text-sm uppercase tracking-widest text-muted-foreground">Rest</p>
            <p class="font-mono text-6xl font-semibold text-primary">{{ restLabel }}</p>
            <button type="button" class="rounded-full border border-border px-5 py-2 text-sm" @click="skipRest">Skip</button>
        </div>

        <div v-else-if="focus.kind === 'setup' && currentBlock" class="flex flex-1 flex-col items-center justify-center gap-6 px-6">
            <p class="text-sm uppercase tracking-widest text-muted-foreground">Setup</p>
            <p class="text-center text-2xl font-semibold">Change equipment, then continue</p>
            <p class="text-sm text-muted-foreground">After block {{ currentBlock.position }}</p>
            <button type="button" class="rounded-full bg-primary px-8 py-3 text-sm font-semibold text-primary-foreground" @click="acknowledgeSetup">
                Setup done
            </button>
        </div>

        <div v-else-if="focus.kind === 'done'" class="flex flex-1 flex-col items-center justify-center gap-6 px-6">
            <p class="text-sm uppercase tracking-widest text-muted-foreground">Complete</p>
            <p class="text-center text-2xl font-semibold">All sets logged</p>
            <button
                type="button"
                class="rounded-full bg-primary px-8 py-3 text-sm font-semibold text-primary-foreground"
                :disabled="workout.status !== 'in_progress'"
                @click="finishWorkout"
            >
                Finish workout
            </button>
        </div>

        <div v-else-if="current" class="flex flex-1 flex-col px-4 py-6">
            <p class="text-xs uppercase tracking-widest text-muted-foreground">
                Block {{ current.block.position }} · {{ groupLabel(current.set.group_type) }} · Set
                {{ current.set.set_index + 1 }}
                <span v-if="current.block.is_superset"> · Superset</span>
            </p>
            <h2 class="mt-2 text-3xl font-semibold leading-tight">{{ current.set.exercise_name }}</h2>
            <p class="mt-2 font-mono text-muted-foreground">
                Target
                <span v-if="current.set.target_weight_kg != null">{{ current.set.target_weight_kg }}{{ workout.weight_unit }}</span>
                <span v-if="current.set.target_reps != null"> × {{ current.set.target_reps }}</span>
                <span v-else> warm-up</span>
            </p>

            <form class="mt-8 flex flex-1 flex-col gap-4" @submit.prevent="completeSet">
                <label class="flex flex-col gap-1 text-sm text-muted-foreground">
                    Weight ({{ workout.weight_unit }})
                    <input
                        v-model.number="setForm.weight_kg"
                        type="number"
                        step="0.5"
                        min="0"
                        class="rounded-xl border border-border bg-card px-4 py-3 text-lg text-foreground"
                        required
                    />
                </label>
                <label class="flex flex-col gap-1 text-sm text-muted-foreground">
                    Reps
                    <input
                        v-model.number="setForm.reps"
                        type="number"
                        min="0"
                        max="100"
                        class="rounded-xl border border-border bg-card px-4 py-3 text-lg text-foreground"
                        required
                    />
                </label>
                <div class="mt-auto flex flex-col gap-3 pb-4">
                    <button
                        type="submit"
                        class="rounded-full bg-primary px-6 py-4 text-base font-semibold text-primary-foreground disabled:opacity-50"
                        :disabled="setForm.processing || workout.status !== 'in_progress'"
                    >
                        Complete set
                    </button>
                    <div v-if="canAddWorkingSet || canRemoveWorkingSet" class="flex gap-2">
                        <button
                            v-if="canAddWorkingSet"
                            type="button"
                            class="flex-1 rounded-md border border-border px-4 py-2.5 text-sm font-medium text-foreground transition-colors hover:bg-secondary"
                            @click="addWorkingSet"
                        >
                            + Set
                        </button>
                        <button
                            v-if="canRemoveWorkingSet"
                            type="button"
                            class="flex-1 rounded-md border border-border px-4 py-2.5 text-sm font-medium text-muted-foreground transition-colors hover:bg-secondary hover:text-foreground"
                            @click="removeWorkingSet"
                        >
                            − Set
                        </button>
                    </div>
                    <button
                        v-if="flatSets.every(({ set }) => set.completed)"
                        type="button"
                        class="rounded-full border border-border px-6 py-3 text-sm"
                        @click="finishWorkout"
                    >
                        Finish workout
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>
