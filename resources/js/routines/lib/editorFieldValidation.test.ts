import { emptyBlock } from '@/routines/lib/blocks';
import { collectRoutineEditorFieldErrors } from '@/routines/lib/editorFieldValidation';
import { describe, expect, it } from 'vitest';

describe('collectRoutineEditorFieldErrors', () => {
    it('accepts a valid single block', () => {
        const block = emptyBlock({ firstCatalogId: 1, prescribedReps: 6 });
        expect(collectRoutineEditorFieldErrors([block])).toEqual({});
    });

    it('rejects cleared or out-of-range sets and target reps', () => {
        const block = emptyBlock({ firstCatalogId: 1, prescribedReps: 6 });
        block.working.set_count = null;
        block.exercises[0].prescribed_reps = null;

        expect(collectRoutineEditorFieldErrors([block])).toEqual({
            'blocks.0.working.set_count': 'Working sets must be a whole number from 1 to 20.',
            'blocks.0.exercises.0.prescribed_reps': 'Target reps must be a whole number from 1 to 100.',
        });
    });

    it('rejects invalid floor and warm-up reps when present', () => {
        const block = emptyBlock({ firstCatalogId: 1, prescribedReps: 6, warmUpDefaults: [{ percent: 40, reps: 5 }] });
        block.exercises[0].achievement_floor = 0;
        block.warm_up.steps[0].reps = Number.NaN;

        const errors = collectRoutineEditorFieldErrors([block]);
        expect(errors['blocks.0.exercises.0.achievement_floor']).toContain('Floor');
        expect(errors['blocks.0.warm_up.steps.0.reps']).toContain('Warm-up reps');
    });

    it('uses rounds wording for circuit set count', () => {
        const block = emptyBlock({ type: 'circuit', firstCatalogId: 1, prescribedReps: 10 });
        block.working.set_count = 0;

        expect(collectRoutineEditorFieldErrors([block])['blocks.0.working.set_count']).toContain('Rounds');
    });
});
