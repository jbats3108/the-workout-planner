export type { PlateProfile } from '@/settings/types';

export type PlayerSetSegment = {
    position: number;
    weight_kg: number;
};

export type PlayerSet = {
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
    has_setup_after: boolean;
    is_dropset: boolean;
    segments: PlayerSetSegment[];
};

export type PlayerBlockExercise = {
    id: number;
    name: string;
    working_weight_kg: number;
    prescribed_reps: number;
    position: number;
};

export type PlayerBlock = {
    id: number;
    position: number;
    is_superset: boolean;
    has_setup_after: boolean;
    has_setup_after_warm_up: boolean;
    exercises: PlayerBlockExercise[];
    sets: PlayerSet[];
};

export type WorkoutPayload = {
    id: number;
    routine_name: string;
    mode: string;
    status: string;
    weight_unit: string;
    blocks: PlayerBlock[];
};

export type SetupPhase = 'after_warm_up' | 'after_block' | 'after_warm_up_step';

export type Focus =
    | { kind: 'set'; blockIndex: number; setId: number }
    | { kind: 'setup'; blockIndex: number; phase: SetupPhase; warmUpStepIndex?: number }
    | { kind: 'done' };

export type Bump = {
    routine_block_exercise_id: number;
    exercise_name: string;
    from_weight_g: number;
    to_weight_g: number;
};

export type UndoBump = {
    bump_record_id: number;
    routine_block_exercise_id: number;
    exercise_name: string;
    from_weight_g: number;
    to_weight_g: number;
};

export type HistoryWorkout = {
    id: number;
    routine_name: string;
    routine_id: number;
    mode: string;
    finished_at: string;
};

export type InProgressWorkout = {
    id: number;
    routine_name: string;
    mode: string;
};
