<script setup lang="ts">
/**
 * Workout player — chrome-minimal full-bleed stage.
 */
import { defaultBarG, gramsToKg, nearestPlateLoad, usesBarbellPlates } from '@/lib/plateCalculator';
import { Head, router, useForm } from '@inertiajs/vue3';
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';

type PlayerSetSegment = {
    position: number;
    weight_kg: number;
};

type PlayerSet = {
    id: number;
    workout_block_exercise_id: number;
    exercise_name: string;
    equipment: string | null;
    set_index: number;
    group_type: 'warm_up' | 'working';
    target_weight_kg: number | null;
    target_reps: number | null;
    logged_weight_kg: number | null;
    logged_reps: number | null;
    completed: boolean;
    rest_seconds: number;
    is_dropset: boolean;
    segments: PlayerSetSegment[];
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
/** Seconds of rest to start after the in-flight complete succeeds; blocks focus advance until then. */
const pendingRestSeconds = ref(0);
/** Last logged working weight per block-exercise — survives rest/focus races better than props alone. */
const lastWorkingWeightKg = ref<Record<number, number>>({});

watch(
    () => props.workout,
    () => {
        if (restSecondsLeft.value > 0 || pendingRestSeconds.value > 0) {
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
    segments: [] as Array<{ weight_kg: number }>,
});

const draftSegments = ref<Array<{ weight_kg: number }>>([]);

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

const workingWeightForSet = (entry: { block: PlayerBlock; set: PlayerSet }): number => {
    const exercise = entry.block.exercises.find((e) => e.id === entry.set.workout_block_exercise_id);
    return exercise?.working_weight_kg ?? entry.set.target_weight_kg ?? 0;
};

const syncDraftFromSet = (entry: { block: PlayerBlock; set: PlayerSet }) => {
    setForm.reps = entry.set.logged_reps ?? entry.set.target_reps ?? 0;
    if (entry.set.is_dropset) {
        draftSegments.value =
            entry.set.segments.length >= 2
                ? entry.set.segments.map((s) => ({ weight_kg: s.weight_kg }))
                : [
                      { weight_kg: workingWeightForSet(entry) },
                      { weight_kg: Math.max(0, workingWeightForSet(entry) - 2.5) },
                  ];
        setForm.weight_kg = draftSegments.value[0]?.weight_kg ?? 0;
        return;
    }
    draftSegments.value = [];
    if (entry.set.group_type === 'warm_up') {
        setForm.weight_kg = entry.set.logged_weight_kg ?? entry.set.target_weight_kg ?? 0;
        return;
    }
    setForm.weight_kg =
        entry.set.logged_weight_kg ??
        previousSetWeightKg(entry) ??
        lastWorkingWeightKg.value[entry.set.workout_block_exercise_id] ??
        entry.set.target_weight_kg ??
        0;
};

watch(
    current,
    (entry) => {
        if (!entry) return;
        syncDraftFromSet(entry);
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
    pendingRestSeconds.value = 0;
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
let wakeLock: WakeLockSentinel | null = null;
let removeVisibilityListener: (() => void) | undefined;

const requestWakeLock = async () => {
    if (!('wakeLock' in navigator)) {
        return;
    }
    try {
        wakeLock = await navigator.wakeLock.request('screen');
        wakeLock.addEventListener('release', () => {
            wakeLock = null;
        });
    } catch {
        // Browser may deny (battery saver, permissions policy, etc.)
    }
};

const releaseWakeLock = async () => {
    try {
        await wakeLock?.release();
    } catch {
        // already released
    }
    wakeLock = null;
};

onBeforeUnmount(() => {
    clearRest();
    removeBeforeListener?.();
    removeVisibilityListener?.();
    window.removeEventListener('beforeunload', onBeforeUnload);
    void releaseWakeLock();
});

onMounted(() => {
    focus.value = firstIncomplete();
    window.addEventListener('beforeunload', onBeforeUnload);
    void requestWakeLock();
    const onVisibility = () => {
        if (document.visibilityState === 'visible') {
            void requestWakeLock();
        }
    };
    document.addEventListener('visibilitychange', onVisibility);
    removeVisibilityListener = () => document.removeEventListener('visibilitychange', onVisibility);
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

    const payload = set.is_dropset
        ? {
              reps: setForm.reps,
              segments: draftSegments.value.map((s) => ({ weight_kg: s.weight_kg })),
          }
        : {
              reps: setForm.reps,
              weight_kg: setForm.weight_kg,
          };

    pendingRestSeconds.value = restAfter;

    setForm
        .transform(() => payload)
        .post(route('workouts.sets.complete', { workout: props.workout.id, set: set.id }), {
            preserveScroll: true,
            only: ['workout'],
            onSuccess: () => {
                if (!set.is_dropset && set.group_type === 'working' && typeof payload.weight_kg === 'number') {
                    lastWorkingWeightKg.value[set.workout_block_exercise_id] = payload.weight_kg;
                }
                if (restAfter > 0) {
                    startRest(restAfter);
                } else {
                    pendingRestSeconds.value = 0;
                    focus.value = firstIncomplete();
                }
            },
            onError: () => {
                pendingRestSeconds.value = 0;
            },
        });
};

const addDropSegment = () => {
    const last = draftSegments.value[draftSegments.value.length - 1]?.weight_kg ?? 10;
    draftSegments.value.push({ weight_kg: Math.max(0, Math.round((last - 2.5) * 2) / 2) });
};

const removeDropSegment = (index: number) => {
    if (draftSegments.value.length <= 2) {
        return;
    }
    draftSegments.value.splice(index, 1);
};

const canPromoteToDropset = computed(
    () =>
        props.workout.status === 'in_progress' &&
        current.value !== null &&
        current.value.set.group_type === 'working' &&
        !current.value.set.completed &&
        !current.value.set.is_dropset &&
        !current.value.block.is_superset,
);

const promoteToDropset = () => {
    if (!current.value || !canPromoteToDropset.value) return;
    const entry = current.value;
    const first = workingWeightForSet(entry);
    const segments = [
        { weight_kg: first },
        { weight_kg: Math.max(0, Math.round((first - 2.5) * 2) / 2) },
    ];
    router.post(
        route('workouts.sets.promote-dropset', { workout: props.workout.id, set: entry.set.id }),
        { segments },
        {
            preserveScroll: true,
            only: ['workout'],
        },
    );
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

const groupLabel = (type: string) => (type === 'warm_up' ? 'Warm-up' : 'Working');

const formatLoadStack = (
    equipment: string | null,
    weightKg: number | null | undefined,
): string | null => {
    if (weightKg == null || Number.isNaN(weightKg) || !usesBarbellPlates(equipment)) {
        return null;
    }
    const barG = defaultBarG(props.plate_profile.bars);
    if (barG === null) return null;
    const load = nearestPlateLoad(Math.round(weightKg * 1000), barG, props.plate_profile.plates);
    if (!load) return null;
    if (!load.per_side.length) {
        return `${gramsToKg(load.bar_g)}${props.workout.weight_unit} bar only`;
    }
    const plates = load.per_side.map((s) => `${s.count}×${gramsToKg(s.denomination_g)}`).join(' + ');
    return `${gramsToKg(load.bar_g)} bar + ${plates} / side`;
};

/** Next incomplete set — shown during Rest / Setup so users know what to load. */
const upcoming = computed(() => {
    const entry = flatSets.value.find(({ set }) => !set.completed) ?? null;
    if (!entry) return null;

    let weightKg: number | null = null;
    let weightLabel: string | null = null;

    if (entry.set.is_dropset) {
        const segments =
            entry.set.segments.length >= 2
                ? entry.set.segments.map((s) => s.weight_kg)
                : [
                      workingWeightForSet(entry),
                      Math.max(0, workingWeightForSet(entry) - 2.5),
                  ];
        weightLabel = segments.join(' → ');
        weightKg = segments[0] ?? null;
    } else if (entry.set.group_type === 'warm_up') {
        weightKg = entry.set.target_weight_kg;
        weightLabel = weightKg != null ? String(weightKg) : null;
    } else {
        weightKg =
            previousSetWeightKg(entry) ??
            lastWorkingWeightKg.value[entry.set.workout_block_exercise_id] ??
            entry.set.target_weight_kg;
        weightLabel = weightKg != null ? String(weightKg) : null;
    }

    return {
        exerciseName: entry.set.exercise_name,
        groupLabel: groupLabel(entry.set.group_type),
        setNumber: entry.set.set_index + 1,
        blockPosition: entry.block.position,
        weightLabel,
        reps: entry.set.target_reps,
        isDropset: entry.set.is_dropset,
        plateStack: formatLoadStack(entry.set.equipment, weightKg),
    };
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
    router.post(route('workouts.working-sets.add', [props.workout.id, current.value.block.id]), {}, {
        preserveScroll: true,
        only: ['workout'],
    });
};

const removeWorkingSet = () => {
    if (!current.value) return;
    router.delete(route('workouts.sets.remove', [props.workout.id, current.value.set.id]), {
        preserveScroll: true,
        only: ['workout'],
    });
};

const plateLoad = computed(() => {
    if (!current.value || !usesBarbellPlates(current.value.set.equipment)) {
        return null;
    }
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
            <div v-if="upcoming" class="mt-2 w-full max-w-sm rounded-xl border border-border bg-card/60 px-4 py-3 text-center">
                <p class="text-xs uppercase tracking-wide text-muted-foreground">Up next</p>
                <p class="mt-1 text-lg font-semibold">{{ upcoming.exerciseName }}</p>
                <p class="mt-1 text-sm text-muted-foreground">
                    Block {{ upcoming.blockPosition }} · {{ upcoming.groupLabel }} · Set {{ upcoming.setNumber }}
                    <span v-if="upcoming.isDropset"> · Dropset</span>
                </p>
                <p v-if="upcoming.weightLabel != null || upcoming.reps != null" class="mt-1 font-mono text-sm text-foreground">
                    <span v-if="upcoming.weightLabel != null">{{ upcoming.weightLabel }}{{ workout.weight_unit }}</span>
                    <span v-if="upcoming.reps != null"> × {{ upcoming.reps }}</span>
                </p>
                <p v-if="upcoming.plateStack" class="mt-2 font-mono text-xs text-muted-foreground">{{ upcoming.plateStack }}</p>
            </div>
            <button type="button" class="rounded-full border border-border px-5 py-2 text-sm" @click="skipRest">Skip</button>
        </div>

        <div v-else-if="focus.kind === 'setup' && currentBlock" class="flex flex-1 flex-col items-center justify-center gap-6 px-6">
            <p class="text-sm uppercase tracking-widest text-muted-foreground">Setup</p>
            <p class="text-center text-2xl font-semibold">Change equipment, then continue</p>
            <p class="text-sm text-muted-foreground">{{ setupHint }}</p>
            <div v-if="upcoming" class="w-full max-w-sm rounded-xl border border-border bg-card/60 px-4 py-3 text-center">
                <p class="text-xs uppercase tracking-wide text-muted-foreground">Up next</p>
                <p class="mt-1 text-lg font-semibold">{{ upcoming.exerciseName }}</p>
                <p class="mt-1 text-sm text-muted-foreground">
                    Block {{ upcoming.blockPosition }} · {{ upcoming.groupLabel }} · Set {{ upcoming.setNumber }}
                    <span v-if="upcoming.isDropset"> · Dropset</span>
                </p>
                <p v-if="upcoming.weightLabel != null || upcoming.reps != null" class="mt-1 font-mono text-sm text-foreground">
                    <span v-if="upcoming.weightLabel != null">{{ upcoming.weightLabel }}{{ workout.weight_unit }}</span>
                    <span v-if="upcoming.reps != null"> × {{ upcoming.reps }}</span>
                </p>
                <p v-if="upcoming.plateStack" class="mt-2 font-mono text-xs text-muted-foreground">{{ upcoming.plateStack }}</p>
            </div>
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
                <span v-if="current.set.is_dropset"> · Dropset</span>
                <span v-if="current.block.is_superset"> · Superset</span>
            </p>
            <h2 class="mt-2 text-3xl font-semibold leading-tight">{{ current.set.exercise_name }}</h2>
            <p class="mt-2 font-mono text-muted-foreground">
                Target
                <template v-if="current.set.is_dropset">
                    {{ draftSegments.map((s) => s.weight_kg).join(' → ') }}{{ workout.weight_unit }}
                    <span v-if="current.set.target_reps != null"> × {{ current.set.target_reps }}</span>
                </template>
                <template v-else>
                    <span v-if="current.set.target_weight_kg != null">{{ current.set.target_weight_kg }}{{ workout.weight_unit }}</span>
                    <span v-if="current.set.target_reps != null"> × {{ current.set.target_reps }}</span>
                </template>
            </p>

            <form class="mt-8 flex flex-1 flex-col gap-4" @submit.prevent="completeSet">
                <template v-if="current.set.is_dropset">
                    <label class="flex flex-col gap-1 text-sm text-muted-foreground">
                        Reps (shared)
                        <input
                            v-model.number="setForm.reps"
                            type="number"
                            min="0"
                            max="100"
                            class="rounded-xl border border-border bg-card px-4 py-3 text-lg text-foreground"
                            required
                        />
                    </label>
                    <div class="space-y-2">
                        <p class="text-xs uppercase tracking-wide text-muted-foreground">Segments</p>
                        <div
                            v-for="(seg, si) in draftSegments"
                            :key="si"
                            class="flex items-center gap-2"
                        >
                            <span class="w-6 font-mono text-xs text-muted-foreground">{{ si + 1 }}</span>
                            <input
                                v-model.number="seg.weight_kg"
                                type="number"
                                step="0.01"
                                min="0"
                                inputmode="decimal"
                                class="flex-1 rounded-xl border border-border bg-card px-4 py-3 text-lg text-foreground"
                                required
                            />
                            <span class="text-sm text-muted-foreground">{{ workout.weight_unit }}</span>
                            <button
                                type="button"
                                class="text-sm text-muted-foreground hover:text-destructive disabled:opacity-30"
                                :disabled="draftSegments.length <= 2"
                                @click="removeDropSegment(si)"
                            >
                                −
                            </button>
                        </div>
                        <button type="button" class="text-sm text-primary" @click="addDropSegment">
                            + Drop
                        </button>
                    </div>
                </template>
                <template v-else>
                    <label class="flex flex-col gap-1 text-sm text-muted-foreground">
                        Weight ({{ workout.weight_unit }})
                        <input
                            v-model.number="setForm.weight_kg"
                            type="number"
                            step="0.01"
                            min="0"
                            inputmode="decimal"
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
                </template>
                <div class="mt-auto flex flex-col gap-3 pb-4">
                    <button
                        type="submit"
                        class="rounded-full bg-primary px-6 py-4 text-base font-semibold text-primary-foreground disabled:opacity-50"
                        :disabled="setForm.processing || workout.status !== 'in_progress'"
                    >
                        {{ current.set.is_dropset ? 'Complete dropset' : 'Complete set' }}
                    </button>
                    <button
                        v-if="canPromoteToDropset"
                        type="button"
                        class="rounded-md border border-border px-4 py-2.5 text-sm font-medium text-foreground transition-colors hover:bg-secondary"
                        @click="promoteToDropset"
                    >
                        Promote to dropset
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
