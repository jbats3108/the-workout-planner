<script setup lang="ts">
/**
 * Workout player — chrome-minimal full-bleed stage.
 */
import { defaultBarG, gramsToKg, nearestPlateLoad } from '@/lib/plateCalculator';
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
    has_setup_after_warm_up: boolean;
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

type PlateProfile = {
    name: string;
    bars: Array<{ name: string; weight_g: number; is_default: boolean }>;
    plates: Array<{ denomination_g: number; count: number; colour: string | null }>;
};

const props = defineProps<{
    workout: WorkoutPayload;
    plate_profile: PlateProfile;
}>();

type SetupPhase = 'after_warm_up' | 'after_block';

type Focus =
    | { kind: 'set'; blockIndex: number; setId: number }
    | { kind: 'setup'; blockIndex: number; phase: SetupPhase }
    | { kind: 'done' };

const setupKey = (blockId: number, phase: SetupPhase) => `${blockId}:${phase}`;

const flatSets = computed(() =>
    props.workout.blocks.flatMap((block, blockIndex) =>
        block.sets.map((set) => ({ blockIndex, block, set })),
    ),
);

const firstIncomplete = (): Focus => {
    for (let blockIndex = 0; blockIndex < props.workout.blocks.length; blockIndex++) {
        const block = props.workout.blocks[blockIndex];
        const warmUps = block.sets.filter((s) => s.group_type === 'warm_up');
        const working = block.sets.filter((s) => s.group_type === 'working');

        const incompleteWarmUp = warmUps.find((s) => !s.completed);
        if (incompleteWarmUp) {
            return { kind: 'set', blockIndex, setId: incompleteWarmUp.id };
        }

        const hasIncompleteWorking = working.some((s) => !s.completed);
        // Only between warm-ups and working — skip if the block has no warm-up sets.
        if (
            block.has_setup_after_warm_up &&
            warmUps.length > 0 &&
            hasIncompleteWorking &&
            !setupDone.value[setupKey(block.id, 'after_warm_up')]
        ) {
            return { kind: 'setup', blockIndex, phase: 'after_warm_up' };
        }

        const incompleteWorking = working.find((s) => !s.completed);
        if (incompleteWorking) {
            return { kind: 'set', blockIndex, setId: incompleteWorking.id };
        }

        if (block.has_setup_after && !setupDone.value[setupKey(block.id, 'after_block')]) {
            return { kind: 'setup', blockIndex, phase: 'after_block' };
        }
    }
    return { kind: 'done' };
};

const setupDone = ref<Record<string, boolean>>({});
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
        // Warm-ups are % of working — always prefer the derived target, not the prior logged warm-up.
        if (entry.set.group_type === 'warm_up') {
            setForm.weight_kg = entry.set.logged_weight_kg ?? entry.set.target_weight_kg ?? 0;
            return;
        }
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

/** True once this set finishes the block's warm-up group (treating the current set as done). */
const finishesWarmUpGroup = (block: PlayerBlock, set: PlayerSet): boolean => {
    if (set.group_type !== 'warm_up') return false;
    return block.sets
        .filter((s) => s.group_type === 'warm_up')
        .every((s) => s.completed || s.id === set.id);
};

const workingRestSeconds = (block: PlayerBlock): number =>
    block.sets.find((s) => s.group_type === 'working')?.rest_seconds ?? 0;

const completeSet = () => {
    if (!current.value || props.workout.status !== 'in_progress') return;
    const { block, set } = current.value;
    let restAfter = shouldRestAfter(block, set) ? set.rest_seconds : 0;
    // When setup-before-working is planned, rest belongs after setup — not before it.
    if (restAfter > 0 && block.has_setup_after_warm_up && finishesWarmUpGroup(block, set)) {
        restAfter = 0;
    }

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
    const phase = focus.value.phase;
    const block = props.workout.blocks[focus.value.blockIndex];
    setupDone.value[setupKey(block.id, phase)] = true;

    if (phase === 'after_warm_up') {
        const rest = workingRestSeconds(block);
        if (rest > 0) {
            startRest(rest);
            return;
        }
    }

    focus.value = firstIncomplete();
};

const setupHint = computed(() => {
    if (focus.value.kind !== 'setup' || !currentBlock.value) return '';
    if (focus.value.phase === 'after_warm_up') {
        return `Block ${currentBlock.value.position} — before working sets`;
    }
    return `After block ${currentBlock.value.position}`;
});

const finishWorkout = () => {
    if (props.workout.status !== 'in_progress') return;
    const incomplete = flatSets.value.some(({ set }) => !set.completed);
    if (incomplete && !confirm('Finish now? Incomplete sets stay incomplete.')) {
        return;
    }
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

const abandonWorkout = () => {
    if (props.workout.status !== 'in_progress') return;
    if (!confirm('Abandon this workout? It will not count as finished.')) return;
    leaveConfirmed.value = true;
    router.post(
        route('workouts.discard', props.workout.id),
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

const plateLoad = computed(() => {
    const barG = defaultBarG(props.plate_profile.bars);
    if (barG === null) return null;
    const targetKg = setForm.weight_kg;
    if (targetKg == null || Number.isNaN(targetKg)) return null;
    return nearestPlateLoad(Math.round(targetKg * 1000), barG, props.plate_profile.plates);
});

const applyNearestLoad = () => {
    if (!plateLoad.value) return;
    setForm.weight_kg = gramsToKg(plateLoad.value.total_g);
};

const formatPlateStack = computed(() => {
    const load = plateLoad.value;
    if (!load) return null;
    if (!load.per_side.length) {
        return `${gramsToKg(load.bar_g)}${props.workout.weight_unit} bar only`;
    }
    const plates = load.per_side.map((s) => `${s.count}×${gramsToKg(s.denomination_g)}`).join(' + ');
    return `${gramsToKg(load.bar_g)} bar + ${plates} / side`;
});
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
            <div class="flex shrink-0 items-center gap-2">
                <div class="font-mono text-sm text-muted-foreground">{{ progressLabel }}</div>
                <button
                    v-if="workout.status === 'in_progress'"
                    type="button"
                    class="rounded-md border border-border px-3 py-1.5 text-sm text-foreground hover:bg-secondary"
                    @click="finishWorkout"
                >
                    Finish
                </button>
                <button
                    v-if="workout.status === 'in_progress'"
                    type="button"
                    class="rounded-md border border-destructive/40 px-3 py-1.5 text-sm text-destructive"
                    @click="abandonWorkout"
                >
                    Abandon
                </button>
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
            <p class="text-sm text-muted-foreground">{{ setupHint }}</p>
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
                <div
                    v-if="plateLoad && formatPlateStack"
                    class="rounded-xl border border-border bg-card/60 px-4 py-3 text-sm"
                >
                    <p class="text-xs uppercase tracking-wide text-muted-foreground">Plates</p>
                    <p class="mt-1 font-mono text-foreground">{{ formatPlateStack }}</p>
                    <p v-if="!plateLoad.exact" class="mt-1 text-xs text-muted-foreground">
                        Nearest loadable:
                        {{ gramsToKg(plateLoad.total_g) }}{{ workout.weight_unit }}
                        <span v-if="plateLoad.delta_g > 0">(+{{ gramsToKg(plateLoad.delta_g) }})</span>
                        <span v-else-if="plateLoad.delta_g < 0">({{ gramsToKg(plateLoad.delta_g) }})</span>
                    </p>
                    <button
                        v-if="!plateLoad.exact"
                        type="button"
                        class="mt-2 text-xs font-medium text-primary hover:underline"
                        @click="applyNearestLoad"
                    >
                        Apply nearest
                    </button>
                </div>
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
                        type="button"
                        class="rounded-full border border-border px-6 py-3 text-sm"
                        :disabled="workout.status !== 'in_progress'"
                        @click="finishWorkout"
                    >
                        Finish workout
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>
