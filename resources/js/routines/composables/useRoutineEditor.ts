import { emptyBlock, normalizeBlock, syncSetupAfterBlockFlags, toggleSuperset } from '@/routines/lib/blocks';
import {
    addDropsetSegment,
    applyRunTheRack,
    dropsetForIndex,
    dropsetSummary,
    isDropsetSlot,
    removeDropsetSegment,
    setSlotKind,
    trimDropsetsToSetCount,
} from '@/routines/lib/dropsets';
import { formatRest } from '@/routines/lib/formatRest';
import { addWarmUpStep, clearWarmUp, removeWarmUpStep, setWarmUpText, warmUpText } from '@/routines/lib/warmUp';
import type { Block, ExerciseOption, RoutinePayload, WarmUpStep } from '@/routines/types';
import type { WarmUpDefaultsScope } from '@/settings/types';
import { router, useForm } from '@inertiajs/vue3';
import { computed, inject, ref, watch, type InjectionKey } from 'vue';

export type EditRoutineProps = {
    routine: RoutinePayload;
    exercises?: ExerciseOption[];
    weight_unit: string;
    warm_up_defaults: WarmUpStep[];
    warm_up_defaults_scope?: WarmUpDefaultsScope;
};

export type RoutineEditor = ReturnType<typeof createRoutineEditor>;

export const routineEditorKey: InjectionKey<RoutineEditor> = Symbol('routineEditor');

export function createRoutineEditor(props: EditRoutineProps) {
    const catalog = computed(() => props.exercises ?? []);

    const defaultWarmUpSteps = (): WarmUpStep[] =>
        (props.warm_up_defaults?.length ? props.warm_up_defaults : []).map((s) => ({
            percent: s.percent,
            reps: s.reps,
        }));

    const firstCatalogId = () => catalog.value[0]?.id ?? null;

    const form = useForm(`EditRoutine:${props.routine.id}`, {
        name: props.routine.name,
        deload_weight_factor: props.routine.deload_weight_factor,
        deload_reps_factor: props.routine.deload_reps_factor,
        // Inertia props are nested reactive proxies — structuredClone cannot clone them
        blocks: props.routine.blocks.length
            ? (() => {
                  const blocks = (JSON.parse(JSON.stringify(props.routine.blocks)) as Block[]).map(normalizeBlock);
                  syncSetupAfterBlockFlags(blocks);

                  return blocks;
              })()
            : ([] as Block[]),
    });

    const active = ref(0);
    const activeExerciseIndex = ref(0);
    const warmUpExpanded = ref(false);
    const dropsetsExpanded = ref(false);
    const deloadExpanded = ref(false);

    const toggleWarmUpExpanded = () => {
        warmUpExpanded.value = !warmUpExpanded.value;
    };

    const toggleDropsetsExpanded = () => {
        dropsetsExpanded.value = !dropsetsExpanded.value;
    };

    const toggleDeloadExpanded = () => {
        deloadExpanded.value = !deloadExpanded.value;
    };

    watch(
        () => form.blocks.length,
        (len) => {
            syncSetupAfterBlockFlags(form.blocks);
            if (active.value >= len) {
                active.value = Math.max(0, len - 1);
            }
            activeExerciseIndex.value = 0;
        },
    );
    watch(active, () => {
        warmUpExpanded.value = false;
        dropsetsExpanded.value = false;
        activeExerciseIndex.value = 0;
    });

    const activeBlock = computed(() => form.blocks[active.value] ?? null);

    const selectBlockExercise = (blockIndex: number, exerciseIndex = 0) => {
        active.value = blockIndex;
        activeExerciseIndex.value = exerciseIndex;
    };

    const exerciseName = (id: number | null) => catalog.value.find((e) => e.id === id)?.name ?? 'Exercise';

    const addBlock = (superset = false) => {
        const seedWarmUp = (props.warm_up_defaults_scope ?? 'all_blocks') === 'all_blocks' || form.blocks.length === 0;
        form.blocks.push(
            emptyBlock({
                superset,
                seedWarmUp,
                warmUpDefaults: defaultWarmUpSteps(),
                firstCatalogId: firstCatalogId(),
            }),
        );
        active.value = form.blocks.length - 1;
    };

    const removeBlock = (index: number) => {
        form.blocks.splice(index, 1);
    };

    const onToggleSuperset = (block: Block) => {
        toggleSuperset(block, firstCatalogId());
    };

    const rackStart = ref(20);
    const rackEnd = ref(10);
    const rackStep = ref(2.5);

    const onApplyRunTheRack = (block: Block, setIndex: number) => {
        applyRunTheRack(block, setIndex, {
            start: rackStart.value,
            end: rackEnd.value,
            step: rackStep.value,
        });
    };

    watch(
        () => form.blocks.map((b) => b.working.set_count),
        () => {
            form.blocks.forEach(trimDropsetsToSetCount);
        },
    );

    const save = () => {
        syncSetupAfterBlockFlags(form.blocks);
        form.transform((data) => ({
            ...data,
            blocks: data.blocks.map((block) => ({
                ...block,
                working: {
                    set_count: block.working.set_count,
                    rest_seconds: block.working.rest_seconds,
                    dropsets: block.is_superset
                        ? []
                        : block.working.dropsets
                              .filter((d) => d.set_index < block.working.set_count && d.segments.length >= 2)
                              .map((d) => ({
                                  set_index: d.set_index,
                                  segments: d.segments.map((s) => ({ weight_kg: s.weight_kg })),
                              })),
                },
            })),
        })).put(route('routines.update', props.routine.id));
    };

    const deleteRoutine = () => {
        if (!confirm(`Delete “${form.name || 'this routine'}”? It will be archived and removed from your list.`)) {
            return;
        }
        router.delete(route('routines.delete', props.routine.id));
    };

    const errorList = computed(() => Object.values(form.errors));

    return {
        form,
        catalog,
        active,
        activeExerciseIndex,
        warmUpExpanded,
        toggleWarmUpExpanded,
        dropsetsExpanded,
        toggleDropsetsExpanded,
        deloadExpanded,
        toggleDeloadExpanded,
        activeBlock,
        selectBlockExercise,
        exerciseName,
        addBlock,
        removeBlock,
        toggleSuperset: onToggleSuperset,
        warmUpText,
        setWarmUpText,
        addWarmUpStep,
        removeWarmUpStep,
        clearWarmUp,
        formatRest,
        dropsetForIndex,
        isDropsetSlot,
        setSlotKind,
        addDropsetSegment,
        removeDropsetSegment,
        trimDropsetsToSetCount,
        dropsetSummary,
        rackStart,
        rackEnd,
        rackStep,
        applyRunTheRack: onApplyRunTheRack,
        save,
        deleteRoutine,
        errorList,
        weightUnit: props.weight_unit,
    };
}

export function useRoutineEditor(): RoutineEditor {
    const editor = inject(routineEditorKey);
    if (!editor) {
        throw new Error('RoutineEditor not provided');
    }
    return editor;
}
