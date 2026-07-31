import { gramsToKg } from '@/lib/plateCalculator';
import { findFirstIncompleteFocus, flattenPlayerSets, setupKey, type FlatSetEntry } from '@/workouts/lib/focus';
import { formatRestSeconds, groupLabel, setupHintText, workoutProgressLabel } from '@/workouts/lib/labels';
import { formatLoadStack, formatPlateStackLabel, resolvePlateLoad } from '@/workouts/lib/plates';
import { preparePlayerInteraction } from '@/workouts/lib/playerInteraction';
import { notifyRestCountdown, notifyRestEnded, shouldBeepRestCountdown } from '@/workouts/lib/restAlert';
import { releaseScreenWake, requestScreenWake } from '@/workouts/lib/screenWake';
import {
    defaultPromoteSegments,
    finishesWarmUpGroup,
    finishesWarmUpStep,
    nextDropSegmentWeight,
    nextSupersetSet,
    plannedSetCount,
    previousSetWeightKg,
    shouldRestAfter,
    supersetRoundSets,
    visitLeavesWorkout,
    warmUpRestSeconds,
    workingRestSeconds,
    workingRoundsInBlock,
    workingWeightForSet,
} from '@/workouts/lib/sets';
import type { Focus, PlateProfile, WorkoutPayload } from '@/workouts/types';
import { router, useForm } from '@inertiajs/vue3';
import { computed, inject, onBeforeUnmount, onMounted, ref, watch, type InjectionKey } from 'vue';

export type PlayWorkoutProps = {
    workout: WorkoutPayload;
    plate_profile: PlateProfile;
};

export type WorkoutPlayer = ReturnType<typeof createWorkoutPlayer>;

export const workoutPlayerKey: InjectionKey<WorkoutPlayer> = Symbol('workoutPlayer');

export function createWorkoutPlayer(props: PlayWorkoutProps) {
    const setupDone = ref<Record<string, boolean>>({});
    const pendingRestSeconds = ref(0);
    const lastWorkingWeightKg = ref<Record<number, number>>({});
    const draftSegments = ref<Array<{ weight_kg: number }>>([]);
    const restSecondsLeft = ref(0);
    const leaveConfirmed = ref(false);
    const logSheetOpen = ref(false);
    /** Stage "Use nearest" override; cleared when focus moves to another set. */
    const stageWeightOverrideKg = ref<number | null>(null);

    let restTimer: ReturnType<typeof setInterval> | null = null;
    let restEndsAt = 0;
    /** Last whole second that already got a countdown beep (avoids double-fire on visibility sync). */
    let lastCountdownBeepSecond: number | null = null;
    let removeBeforeListener: (() => void) | undefined;
    let removeVisibilityListener: (() => void) | undefined;
    let removeRestVisibilityListener: (() => void) | undefined;

    const flatSets = computed(() => flattenPlayerSets(props.workout.blocks));

    const firstIncomplete = (): Focus => findFirstIncompleteFocus(props.workout.blocks, setupDone.value);

    const focus = ref<Focus>(firstIncomplete());

    const setForm = useForm({
        reps: 0,
        weight_kg: 0,
        segments: [] as Array<{ weight_kg: number }>,
    });

    const clearRest = () => {
        if (restTimer) {
            clearInterval(restTimer);
            restTimer = null;
        }
        restEndsAt = 0;
        restSecondsLeft.value = 0;
        lastCountdownBeepSecond = null;
        removeRestVisibilityListener?.();
        removeRestVisibilityListener = undefined;
    };

    const finishRest = () => {
        clearRest();
        notifyRestEnded();
        focus.value = firstIncomplete();
    };

    const syncRestFromClock = () => {
        if (restEndsAt <= 0) {
            return;
        }

        const remaining = Math.ceil((restEndsAt - Date.now()) / 1000);
        if (remaining <= 0) {
            finishRest();
            return;
        }

        restSecondsLeft.value = remaining;

        if (shouldBeepRestCountdown(remaining) && lastCountdownBeepSecond !== remaining) {
            lastCountdownBeepSecond = remaining;
            notifyRestCountdown(remaining);
        }
    };

    const startRest = (seconds: number) => {
        clearRest();
        pendingRestSeconds.value = 0;
        if (seconds <= 0) {
            focus.value = firstIncomplete();
            return;
        }

        restEndsAt = Date.now() + seconds * 1000;
        restSecondsLeft.value = seconds;
        syncRestFromClock();
        restTimer = setInterval(syncRestFromClock, 1000);

        const onRestVisibility = () => {
            if (restEndsAt > 0) {
                syncRestFromClock();
            }
        };
        document.addEventListener('visibilitychange', onRestVisibility);
        removeRestVisibilityListener = () => document.removeEventListener('visibilitychange', onRestVisibility);
    };

    watch(
        () => props.workout,
        () => {
            if (restSecondsLeft.value > 0 || pendingRestSeconds.value > 0) {
                return;
            }
            // Keep focus when add/remove only changes set count — don't jump to another set.
            if (focus.value.kind === 'set') {
                const focused = flatSets.value.find(({ set }) => set.id === (focus.value as { setId: number }).setId);
                if (focused && !focused.set.completed) {
                    return;
                }
            }
            focus.value = firstIncomplete();
        },
        { deep: true },
    );

    const current = computed(() => {
        if (focus.value.kind !== 'set') {
            return null;
        }
        return flatSets.value.find(({ set }) => set.id === (focus.value as { setId: number }).setId) ?? null;
    });

    const currentBlock = computed(() => {
        if (focus.value.kind === 'done') {
            return null;
        }
        return props.workout.blocks[focus.value.blockIndex] ?? null;
    });

    const syncDraftFromSet = (entry: FlatSetEntry) => {
        setForm.reps = entry.set.logged_reps ?? entry.set.target_reps ?? 0;
        if (entry.set.is_dropset) {
            draftSegments.value =
                entry.set.segments.length >= 2
                    ? entry.set.segments.map((s) => ({ weight_kg: s.weight_kg }))
                    : defaultPromoteSegments(workingWeightForSet(entry));
            setForm.weight_kg = draftSegments.value[0]?.weight_kg ?? 0;
            return;
        }
        draftSegments.value = [];
        if (stageWeightOverrideKg.value != null) {
            setForm.weight_kg = stageWeightOverrideKg.value;
            return;
        }
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
        (entry, previous) => {
            logSheetOpen.value = false;
            if (entry?.set.id !== previous?.set.id) {
                stageWeightOverrideKg.value = null;
            }
            if (!entry) {
                return;
            }
            syncDraftFromSet(entry);
        },
        { immediate: true },
    );

    const progressLabel = computed(() => workoutProgressLabel(flatSets.value));
    const restLabel = computed(() => formatRestSeconds(restSecondsLeft.value));

    const onBeforeUnload = (event: BeforeUnloadEvent) => {
        if (props.workout.status !== 'in_progress') {
            return;
        }
        event.preventDefault();
        event.returnValue = '';
    };

    const requestWakeLock = async () => {
        await requestScreenWake();
    };

    const releaseWakeLock = async () => {
        await releaseScreenWake();
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
            if (leaveConfirmed.value) {
                return;
            }
            if (props.workout.status !== 'in_progress') {
                return;
            }
            if (!visitLeavesWorkout(event.detail.visit, props.workout.id)) {
                return;
            }
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

    const openLogSheet = () => {
        if (!current.value || props.workout.status !== 'in_progress') {
            return;
        }
        preparePlayerInteraction();
        syncDraftFromSet(current.value);
        logSheetOpen.value = true;
    };

    const cancelLogSheet = () => {
        logSheetOpen.value = false;
    };

    const completeSet = () => {
        if (!current.value || props.workout.status !== 'in_progress' || !logSheetOpen.value) {
            return;
        }
        preparePlayerInteraction();
        logSheetOpen.value = false;
        const { block, set } = current.value;
        let restAfter = shouldRestAfter(block, set) ? set.rest_seconds : 0;
        if (restAfter > 0 && block.has_setup_after_warm_up && finishesWarmUpGroup(block, set)) {
            restAfter = 0;
        }
        if (restAfter > 0 && finishesWarmUpStep(block, set)) {
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
        draftSegments.value.push({ weight_kg: nextDropSegmentWeight(last) });
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
        if (!current.value || !canPromoteToDropset.value) {
            return;
        }
        const entry = current.value;
        router.post(
            route('workouts.sets.promote-dropset', { workout: props.workout.id, set: entry.set.id }),
            { segments: defaultPromoteSegments(workingWeightForSet(entry)) },
            {
                preserveScroll: true,
                only: ['workout'],
            },
        );
    };

    const skipRest = () => {
        preparePlayerInteraction();
        clearRest();
        focus.value = firstIncomplete();
    };

    const acknowledgeSetup = () => {
        if (focus.value.kind !== 'setup') {
            return;
        }
        preparePlayerInteraction();
        const phase = focus.value.phase;
        const block = props.workout.blocks[focus.value.blockIndex];
        setupDone.value[setupKey(block.id, phase, focus.value.warmUpStepIndex)] = true;

        if (phase === 'after_warm_up') {
            const rest = workingRestSeconds(block);
            if (rest > 0) {
                startRest(rest);
                return;
            }
        }

        if (phase === 'after_warm_up_step') {
            const rest = warmUpRestSeconds(block);
            if (rest > 0) {
                startRest(rest);
                return;
            }
        }

        focus.value = firstIncomplete();
    };

    const setupHint = computed(() => setupHintText(focus.value, currentBlock.value));

    const supersetNext = computed(() => {
        if (!current.value) {
            return null;
        }

        const nextSet = nextSupersetSet(current.value.block, current.value.set);
        if (!nextSet) {
            return null;
        }

        const entry = { blockIndex: current.value.blockIndex, block: current.value.block, set: nextSet };
        let weightKg: number | null = null;

        if (nextSet.group_type === 'warm_up') {
            weightKg = nextSet.target_weight_kg;
        } else {
            weightKg = previousSetWeightKg(entry) ?? lastWorkingWeightKg.value[nextSet.workout_block_exercise_id] ?? nextSet.target_weight_kg;
        }

        const targetParts: string[] = [];
        if (weightKg != null) {
            targetParts.push(`${weightKg}${props.workout.weight_unit}`);
        }
        if (nextSet.target_reps != null) {
            targetParts.push(`× ${nextSet.target_reps}`);
        }

        return {
            exerciseName: nextSet.exercise_name,
            targetLabel: targetParts.length > 0 ? targetParts.join(' ') : null,
            label: targetParts.length > 0 ? `Then: ${nextSet.exercise_name} (${targetParts.join(' ')})` : `Then: ${nextSet.exercise_name}`,
        };
    });

    const previewForEntry = (entry: FlatSetEntry, letter: string | null = null) => {
        let weightKg: number | null = null;
        let weightLabel: string | null = null;

        if (entry.set.is_dropset) {
            const segments =
                entry.set.segments.length >= 2
                    ? entry.set.segments.map((s) => s.weight_kg)
                    : defaultPromoteSegments(workingWeightForSet(entry)).map((s) => s.weight_kg);
            weightLabel = segments.join(' → ');
            weightKg = segments[0] ?? null;
        } else if (entry.set.group_type === 'warm_up') {
            weightKg = entry.set.target_weight_kg;
            weightLabel = weightKg != null ? String(weightKg) : null;
        } else {
            weightKg = previousSetWeightKg(entry) ?? lastWorkingWeightKg.value[entry.set.workout_block_exercise_id] ?? entry.set.target_weight_kg;
            weightLabel = weightKg != null ? String(weightKg) : null;
        }

        return {
            exerciseName: entry.set.exercise_name,
            groupLabel: groupLabel(entry.set.group_type),
            setNumber: entry.set.set_index + 1,
            setCount: plannedSetCount(entry.block, entry.set),
            blockPosition: entry.block.position,
            weightLabel,
            reps: entry.set.target_reps,
            isDropset: entry.set.is_dropset,
            plateStack: formatLoadStack(entry.set.equipment, weightKg, props.plate_profile, props.workout.weight_unit),
            letter,
        };
    };

    const upcoming = computed(() => {
        const entry = flatSets.value.find(({ set }) => !set.completed) ?? null;
        if (!entry) {
            return null;
        }

        return previewForEntry(entry);
    });

    /** Both exercises in the upcoming superset round — only while on Setup. */
    const setupSupersetPair = computed(() => {
        if (focus.value.kind !== 'setup') {
            return null;
        }

        const entry = flatSets.value.find(({ set }) => !set.completed) ?? null;
        if (!entry?.block.is_superset) {
            return null;
        }

        const round = supersetRoundSets(entry.block, entry.set);
        if (round.length < 2) {
            return null;
        }

        return round.map((set, index) => previewForEntry({ blockIndex: entry.blockIndex, block: entry.block, set }, String.fromCharCode(65 + index)));
    });

    const finishWorkout = () => {
        if (props.workout.status !== 'in_progress') {
            return;
        }
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
        if (props.workout.status !== 'in_progress') {
            return;
        }
        if (!confirm('Abandon this workout? It will not count as finished.')) {
            return;
        }
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

    const roundsInBlock = computed(() => (current.value ? workingRoundsInBlock(current.value.block) : 0));

    const canAddWorkingSet = computed(
        () => props.workout.status === 'in_progress' && current.value !== null && current.value.set.group_type === 'working',
    );

    const canRemoveWorkingSet = computed(() => {
        if (!current.value || props.workout.status !== 'in_progress') {
            return false;
        }
        if (current.value.set.group_type !== 'working' || current.value.set.completed) {
            return false;
        }
        if (roundsInBlock.value <= 1) {
            return false;
        }

        const index = current.value.set.set_index;
        const hasLaterRound = current.value.block.sets.some((s) => s.group_type === 'working' && s.set_index > index);
        if (!hasLaterRound) {
            // Last round: removing it would skip straight to the next block/setup.
            return false;
        }

        const round = current.value.block.sets.filter((s) => s.group_type === 'working' && s.set_index === index);
        return round.every((s) => !s.completed);
    });

    const addWorkingSet = () => {
        if (!current.value) {
            return;
        }
        router.post(
            route('workouts.working-sets.add', [props.workout.id, current.value.block.id]),
            {},
            {
                preserveScroll: true,
                only: ['workout'],
            },
        );
    };

    const removeWorkingSet = () => {
        if (!current.value || !canRemoveWorkingSet.value) {
            return;
        }
        router.delete(route('workouts.sets.remove', [props.workout.id, current.value.set.id]), {
            preserveScroll: true,
            only: ['workout'],
        });
    };

    const plateLoad = computed(() => {
        if (!current.value) {
            return null;
        }
        return resolvePlateLoad(setForm.weight_kg, current.value.set.equipment, props.plate_profile);
    });

    const stageWeightKg = computed(() => {
        if (!current.value) {
            return null;
        }
        const entry = current.value;
        if (entry.set.is_dropset) {
            return null;
        }
        if (stageWeightOverrideKg.value != null) {
            return stageWeightOverrideKg.value;
        }
        if (entry.set.group_type === 'warm_up') {
            return entry.set.logged_weight_kg ?? entry.set.target_weight_kg ?? null;
        }
        return (
            entry.set.logged_weight_kg ??
            previousSetWeightKg(entry) ??
            lastWorkingWeightKg.value[entry.set.workout_block_exercise_id] ??
            entry.set.target_weight_kg ??
            null
        );
    });

    const stageDropsetWeights = computed(() => {
        if (!current.value?.set.is_dropset) {
            return [] as number[];
        }
        const entry = current.value;
        if (entry.set.segments.length >= 2) {
            return entry.set.segments.map((segment) => segment.weight_kg);
        }
        return defaultPromoteSegments(workingWeightForSet(entry)).map((segment) => segment.weight_kg);
    });

    const stagePlateLoad = computed(() => {
        if (!current.value || current.value.set.is_dropset) {
            return null;
        }
        const weight = stageWeightKg.value;
        if (weight == null) {
            return null;
        }
        return resolvePlateLoad(weight, current.value.set.equipment, props.plate_profile);
    });

    const applyNearestLoad = () => {
        const load = plateLoad.value ?? stagePlateLoad.value;
        if (!load) {
            return;
        }
        setForm.weight_kg = gramsToKg(load.total_g);
    };

    const applyStageNearestLoad = () => {
        if (!stagePlateLoad.value) {
            return;
        }
        const nearestKg = gramsToKg(stagePlateLoad.value.total_g);
        stageWeightOverrideKg.value = nearestKg;
        setForm.weight_kg = nearestKg;
    };

    const formatPlateStack = computed(() => {
        const load = plateLoad.value;
        if (!load) {
            return null;
        }
        return formatPlateStackLabel(load, props.workout.weight_unit);
    });

    const stageFormatPlateStack = computed(() => {
        const load = stagePlateLoad.value;
        if (!load) {
            return null;
        }
        return formatPlateStackLabel(load, props.workout.weight_unit);
    });

    return {
        workout: computed(() => props.workout),
        plateProfile: computed(() => props.plate_profile),
        focus,
        current,
        currentBlock,
        setForm,
        draftSegments,
        restSecondsLeft,
        restLabel,
        logSheetOpen,
        progressLabel,
        upcoming,
        setupHint,
        setupSupersetPair,
        supersetNext,
        canPromoteToDropset,
        canAddWorkingSet,
        canRemoveWorkingSet,
        plateLoad,
        stagePlateLoad,
        stageWeightKg,
        stageDropsetWeights,
        formatPlateStack,
        stageFormatPlateStack,
        groupLabel,
        gramsToKg,
        openLogSheet,
        cancelLogSheet,
        completeSet,
        addDropSegment,
        removeDropSegment,
        promoteToDropset,
        skipRest,
        acknowledgeSetup,
        finishWorkout,
        abandonWorkout,
        leaveWorkout,
        addWorkingSet,
        removeWorkingSet,
        applyNearestLoad,
        applyStageNearestLoad,
    };
}

export function useWorkoutPlayer(): WorkoutPlayer {
    const player = inject(workoutPlayerKey);
    if (!player) {
        throw new Error('WorkoutPlayer not provided');
    }
    return player;
}
