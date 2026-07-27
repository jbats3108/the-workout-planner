import { exerciseOptionsFor, filterExercises } from '@/routines/lib/catalog';
import { exerciseOption } from '@/test/factories';
import { describe, expect, it } from 'vitest';

describe('filterExercises', () => {
    const catalog = [
        exerciseOption({ id: 1, name: 'Bench Press', primary_muscle_group: 'Chest' }),
        exerciseOption({ id: 2, name: 'Row', primary_muscle_group: 'Back' }),
    ];

    it('filters by name and muscle group', () => {
        expect(filterExercises(catalog, 'back')).toHaveLength(1);
        expect(filterExercises(catalog, '')).toHaveLength(2);
    });
});

describe('exerciseOptionsFor', () => {
    const catalog = [exerciseOption({ id: 1, name: 'A' }), exerciseOption({ id: 2, name: 'B' })];

    it('prepends selected exercise when filtered out', () => {
        const filtered = [catalog[1]];
        const options = exerciseOptionsFor(catalog, filtered, 1);
        expect(options[0].id).toBe(1);
        expect(options).toHaveLength(2);
    });
});
