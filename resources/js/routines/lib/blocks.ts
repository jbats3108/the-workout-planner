import type { Block, BlockExercise, WarmUpStep } from '@/routines/types';

export function emptyExercise(firstCatalogId: number | null = null): BlockExercise {
    return {
        exercise_id: firstCatalogId,
        working_weight_kg: 60,
        prescribed_reps: 6,
        achievement_floor: null,
        progression_target: null,
    };
}

export function emptyBlock(
    options: {
        superset?: boolean;
        seedWarmUp?: boolean;
        warmUpDefaults?: WarmUpStep[];
        firstCatalogId?: number | null;
    } = {},
): Block {
    const { superset = false, seedWarmUp = true, warmUpDefaults = [], firstCatalogId = null } = options;
    const steps = seedWarmUp ? warmUpDefaults.map((s) => ({ percent: s.percent, reps: s.reps })) : [];
    return {
        is_superset: superset,
        has_setup_after: false,
        has_setup_after_warm_up: false,
        exercises: superset ? [emptyExercise(firstCatalogId), emptyExercise(firstCatalogId)] : [emptyExercise(firstCatalogId)],
        working: { set_count: 3, rest_seconds: 120, dropsets: [] },
        warm_up: { set_count: steps.length, rest_seconds: 60, steps },
    };
}

export function normalizeBlock(raw: Block): Block {
    const steps = (raw.warm_up?.steps ?? []).map((s) => ({
        percent: Number(s.percent),
        reps: Number(s.reps ?? 5),
    }));
    const dropsets = (raw.working?.dropsets ?? [])
        .map((d) => ({
            set_index: Number(d.set_index),
            segments: (d.segments ?? []).map((s) => ({ weight_kg: Number(s.weight_kg) })),
        }))
        .filter((d) => d.segments.length >= 2);
    return {
        ...raw,
        has_setup_after_warm_up: Boolean(raw.has_setup_after_warm_up) && steps.length > 0,
        working: {
            set_count: raw.working?.set_count ?? 3,
            rest_seconds: raw.working?.rest_seconds ?? 120,
            dropsets: raw.is_superset ? [] : dropsets,
        },
        warm_up: {
            set_count: raw.warm_up?.set_count ?? steps.length,
            rest_seconds: raw.warm_up?.rest_seconds ?? 60,
            steps,
        },
    };
}

export function toggleSuperset(block: Block, firstCatalogId: number | null = null): void {
    block.is_superset = !block.is_superset;
    if (block.is_superset && block.exercises.length < 2) {
        block.exercises.push(emptyExercise(firstCatalogId));
    }
    if (!block.is_superset && block.exercises.length > 1) {
        block.exercises = [block.exercises[0]];
    }
    if (block.is_superset) {
        block.working.dropsets = [];
    }
}
