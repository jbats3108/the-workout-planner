import { playerBlock, playerSet } from '@/test/factories';
import { flattenPlayerSets } from '@/workouts/lib/focus';
import {
    defaultPromoteSegments,
    finishesWarmUpGroup,
    finishesWarmUpStep,
    nextDropSegmentWeight,
    nextSupersetSet,
    plannedSetCount,
    previousSetWeightKg,
    shouldRestAfter,
    visitLeavesWorkout,
    workingWeightForSet,
} from '@/workouts/lib/sets';
import { describe, expect, it } from 'vitest';

describe('previousSetWeightKg', () => {
    it('returns last completed weight for same exercise', () => {
        const block = playerBlock({
            sets: [playerSet({ id: 1, set_index: 0, completed: true, logged_weight_kg: 80 }), playerSet({ id: 2, set_index: 1, completed: false })],
        });
        const entry = flattenPlayerSets([block])[1];
        expect(previousSetWeightKg(entry)).toBe(80);
    });
});

describe('workingWeightForSet', () => {
    it('prefers exercise working weight', () => {
        const block = playerBlock();
        const entry = flattenPlayerSets([block])[0];
        expect(workingWeightForSet(entry)).toBe(100);
    });
});

describe('shouldRestAfter', () => {
    it('waits for superset round completion', () => {
        const block = playerBlock({
            is_superset: true,
            exercises: [
                { id: 10, name: 'A', working_weight_kg: 50, prescribed_reps: 8, position: 0 },
                { id: 11, name: 'B', working_weight_kg: 50, prescribed_reps: 8, position: 1 },
            ],
            sets: [
                playerSet({ id: 1, workout_block_exercise_id: 10, completed: true }),
                playerSet({ id: 2, workout_block_exercise_id: 11, completed: false, set_index: 0 }),
            ],
        });
        const current = block.sets[1];
        expect(shouldRestAfter(block, current)).toBe(true);
    });
});

describe('nextSupersetSet', () => {
    it('returns the partner exercise later in the round', () => {
        const block = playerBlock({
            is_superset: true,
            exercises: [
                { id: 10, name: 'Press', working_weight_kg: 50, prescribed_reps: 8, position: 0 },
                { id: 11, name: 'Row', working_weight_kg: 50, prescribed_reps: 8, position: 1 },
            ],
            sets: [
                playerSet({ id: 1, workout_block_exercise_id: 10, exercise_name: 'Press', set_index: 0 }),
                playerSet({ id: 2, workout_block_exercise_id: 11, exercise_name: 'Row', set_index: 0 }),
            ],
        });
        expect(nextSupersetSet(block, block.sets[0])?.exercise_name).toBe('Row');
        expect(nextSupersetSet(block, block.sets[1])).toBeNull();
    });

    it('returns null outside supersets', () => {
        const block = playerBlock();
        expect(nextSupersetSet(block, block.sets[0])).toBeNull();
    });
});

describe('finishesWarmUpStep', () => {
    it('is true when a mid warm-up round completes with setup after', () => {
        const block = playerBlock({
            sets: [
                playerSet({ id: 1, group_type: 'warm_up', set_index: 0, completed: true, has_setup_after: true }),
                playerSet({ id: 2, group_type: 'warm_up', set_index: 1, completed: false, has_setup_after: false }),
            ],
        });
        expect(finishesWarmUpStep(block, block.sets[0])).toBe(true);
    });

    it('is false on the last warm-up step even when flagged', () => {
        const block = playerBlock({
            sets: [
                playerSet({ id: 1, group_type: 'warm_up', set_index: 0, completed: true, has_setup_after: false }),
                playerSet({ id: 2, group_type: 'warm_up', set_index: 1, completed: false, has_setup_after: true }),
            ],
        });
        expect(finishesWarmUpStep(block, block.sets[1])).toBe(false);
    });
});

describe('finishesWarmUpGroup', () => {
    it('is true on last warm-up set', () => {
        const block = playerBlock({
            sets: [
                playerSet({ id: 1, group_type: 'warm_up', completed: true }),
                playerSet({ id: 2, group_type: 'warm_up', completed: false, set_index: 1 }),
            ],
        });
        expect(finishesWarmUpGroup(block, block.sets[1])).toBe(true);
    });
});

describe('plannedSetCount', () => {
    it('counts sets in the same group for the exercise', () => {
        const block = playerBlock({
            sets: [
                playerSet({ id: 1, set_index: 0 }),
                playerSet({ id: 2, set_index: 1 }),
                playerSet({ id: 3, set_index: 2 }),
                playerSet({ id: 4, group_type: 'warm_up', set_index: 0 }),
            ],
        });
        expect(plannedSetCount(block, block.sets[1])).toBe(3);
    });
});

describe('dropset helpers', () => {
    it('steps down by 2.5kg', () => {
        expect(nextDropSegmentWeight(60)).toBe(57.5);
        expect(defaultPromoteSegments(60)).toEqual([{ weight_kg: 60 }, { weight_kg: 57.5 }]);
    });
});

describe('visitLeavesWorkout', () => {
    it('detects navigation away from workout', () => {
        expect(visitLeavesWorkout({ url: '/dashboard' }, 5)).toBe(true);
        expect(visitLeavesWorkout({ url: '/workouts/5' }, 5)).toBe(false);
    });
});
