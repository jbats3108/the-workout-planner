import type { WarmUpStep } from '@/settings/types';

export type { WarmUpStep } from '@/settings/types';

export type ExerciseOption = {
    id: number;
    name: string;
    primary_muscle_group: string;
};

export type BlockExercise = {
    exercise_id: number | null;
    working_weight_kg: number;
    prescribed_reps: number;
    achievement_floor: number | null;
    progression_target: number | null;
};

export type DropsetSegment = {
    weight_kg: number;
};

export type DropsetRecipe = {
    set_index: number;
    segments: DropsetSegment[];
};

export type Block = {
    is_superset: boolean;
    has_setup_after: boolean;
    has_setup_after_warm_up: boolean;
    exercises: BlockExercise[];
    working: { set_count: number; rest_seconds: number; dropsets: DropsetRecipe[] };
    warm_up: { set_count: number; rest_seconds: number; steps: WarmUpStep[] };
};

export type RoutinePayload = {
    id: number;
    name: string;
    deload_weight_factor: number;
    deload_reps_factor: number;
    blocks: Block[];
};

/** Dashboard / list row for a routine the user owns. */
export type Routine = {
    id: number;
    name: string;
    deload_weight_factor?: number | null;
    deload_reps_factor?: number | null;
    can_start?: boolean;
};
