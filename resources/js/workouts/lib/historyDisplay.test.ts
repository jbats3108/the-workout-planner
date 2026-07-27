import { formatKg, historyRowsForBlock } from '@/workouts/lib/historyDisplay';
import type { PlayerSet } from '@/workouts/types';
import { describe, expect, it } from 'vitest';

const set = (overrides: Partial<PlayerSet> & Pick<PlayerSet, 'id' | 'group_type'>): PlayerSet => ({
    workout_block_exercise_id: 1,
    exercise_name: 'Bench',
    equipment: 'barbell',
    set_index: 0,
    target_weight_kg: null,
    target_reps: null,
    logged_weight_kg: null,
    logged_reps: null,
    completed: true,
    rest_seconds: 60,
    is_dropset: false,
    segments: [],
    ...overrides,
});

describe('formatKg', () => {
    it('formats whole and 2dp weights', () => {
        expect(formatKg(80)).toBe('80');
        expect(formatKg(28.75)).toBe('28.75');
        expect(formatKg(28.7500001)).toBe('28.75');
        expect(formatKg(null)).toBe('—');
    });
});

describe('historyRowsForBlock', () => {
    it('collapses contiguous warm-ups into one row', () => {
        const rows = historyRowsForBlock([
            set({ id: 1, group_type: 'warm_up', logged_reps: 5, logged_weight_kg: 40 }),
            set({ id: 2, group_type: 'warm_up', logged_reps: 3, logged_weight_kg: 50 }),
            set({ id: 3, group_type: 'working', logged_reps: 6, logged_weight_kg: 80 }),
            set({ id: 4, group_type: 'working', logged_reps: 6, logged_weight_kg: 80 }),
        ]);

        expect(rows).toHaveLength(3);
        expect(rows[0]).toMatchObject({ type: 'warm_up', sets: [{ id: 1 }, { id: 2 }] });
        expect(rows[1]).toMatchObject({ type: 'working', set: { id: 3 } });
        expect(rows[2]).toMatchObject({ type: 'working', set: { id: 4 } });
    });
});
