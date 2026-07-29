import { playerBlock, playerSet } from '@/test/factories';
import { flattenPlayerSets } from '@/workouts/lib/focus';
import {
    defaultPromoteSegments,
    finishesWarmUpGroup,
    nextDropSegmentWeight,
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

describe('dropset helpers', () => {
    it('steps down by 2.5kg', () => {
        expect(nextDropSegmentWeight(60)).toBe(57.5);
        expect(defaultPromoteSegments(60)).toEqual([{ weight_kg: 60 }, { weight_kg: 57.5 }]);
    });
});

describe('visitLeavesWorkout', () => {
    it('detects navigation away from workout', () => {
        expect(visitLeavesWorkout({ url: '/dashboard' }, '01TESTULID')).toBe(true);
        expect(visitLeavesWorkout({ url: '/workouts/01TESTULID' }, '01TESTULID')).toBe(false);
    });
});
