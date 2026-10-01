import type { Block } from '@/routines/types';

function isIntegerInRange(value: number | null | undefined, min: number, max: number): value is number {
    return value != null && Number.isInteger(value) && value >= min && value <= max;
}

/**
 * Client-side required/range checks for clearable stepper fields before save.
 * Keys match Laravel nested attribute paths used by SyncRoutineData.
 */
export function collectRoutineEditorFieldErrors(blocks: Block[]): Record<string, string> {
    const errors: Record<string, string> = {};

    blocks.forEach((block, blockIndex) => {
        const setCount = block.working.set_count;
        if (!isIntegerInRange(setCount, 1, 20)) {
            errors[`blocks.${blockIndex}.working.set_count`] =
                block.type === 'circuit' ? 'Rounds must be a whole number from 1 to 20.' : 'Working sets must be a whole number from 1 to 20.';
        }

        block.exercises.forEach((exercise, exerciseIndex) => {
            const isTimed = exercise.prescription_mode === 'duration';
            if (isTimed) {
                const duration = exercise.prescribed_duration_seconds;
                if (!isIntegerInRange(duration ?? null, 1, 3600)) {
                    errors[`blocks.${blockIndex}.exercises.${exerciseIndex}.prescribed_duration_seconds`] =
                        'Duration must be a whole number from 1 to 3600 seconds.';
                }
            } else if (!isIntegerInRange(exercise.prescribed_reps, 1, 100)) {
                errors[`blocks.${blockIndex}.exercises.${exerciseIndex}.prescribed_reps`] = 'Target reps must be a whole number from 1 to 100.';
            }

            if (!isTimed && block.type !== 'circuit' && exercise.achievement_floor != null && !isIntegerInRange(exercise.achievement_floor, 1, 100)) {
                errors[`blocks.${blockIndex}.exercises.${exerciseIndex}.achievement_floor`] = 'Floor must be a whole number from 1 to 100.';
            }
        });

        if (block.type !== 'circuit') {
            block.warm_up.steps.forEach((step, stepIndex) => {
                if (!isIntegerInRange(step.reps, 1, 100)) {
                    errors[`blocks.${blockIndex}.warm_up.steps.${stepIndex}.reps`] = 'Warm-up reps must be a whole number from 1 to 100.';
                }
            });
        }
    });

    return errors;
}
