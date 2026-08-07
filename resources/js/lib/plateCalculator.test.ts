import {
    defaultBarG,
    formatGramsToKg,
    gramsToKg,
    loadFromPlateStack,
    nearestPlateLoad,
    plateStackFromLoad,
    updatePlateCount,
    usesBarbellPlates,
} from '@/lib/plateCalculator';
import { describe, expect, it } from 'vitest';

describe('plateCalculator', () => {
    const plates = [
        { denomination_g: 25000, count: 2, colour: null },
        { denomination_g: 20000, count: 2, colour: null },
        { denomination_g: 15000, count: 2, colour: null },
        { denomination_g: 10000, count: 4, colour: null },
        { denomination_g: 5000, count: 4, colour: null },
        { denomination_g: 2500, count: 4, colour: null },
        { denomination_g: 1250, count: 4, colour: null },
    ];

    it('finds nearest loadable weight', () => {
        const load = nearestPlateLoad(80000, 20000, plates);
        expect(load?.total_g).toBe(80000);
        expect(load?.exact).toBe(true);
    });

    it('prefers the heaviest available first plate', () => {
        const load = nearestPlateLoad(80000, 20000, plates);

        expect(load?.per_side).toEqual([
            { denomination_g: 25000, count: 1, colour: null },
            { denomination_g: 5000, count: 1, colour: null },
        ]);
    });

    it('keeps the previous stack when it is a valid target', () => {
        const previous = nearestPlateLoad(80000, 20000, plates);
        const alternate = loadFromPlateStack(
            80000,
            {
                bar_g: 20000,
                per_side: [
                    { denomination_g: 20000, count: 1 },
                    { denomination_g: 10000, count: 1 },
                ],
            },
            plates,
        );

        const load = nearestPlateLoad(80000, 20000, plates, alternate);

        expect(previous?.per_side).not.toEqual(load?.per_side);
        expect(load?.per_side).toEqual(alternate?.per_side);
    });

    it('builds and edits a mirrored plate stack', () => {
        const stack = loadFromPlateStack(
            80000,
            {
                bar_g: 20000,
                per_side: [
                    { denomination_g: 25000, count: 1 },
                    { denomination_g: 5000, count: 1 },
                ],
            },
            plates,
        );

        expect(stack?.total_g).toBe(80000);
        expect(plateStackFromLoad(stack!)).toEqual({
            bar_g: 20000,
            per_side: [
                { denomination_g: 25000, count: 1 },
                { denomination_g: 5000, count: 1 },
            ],
        });

        const edited = updatePlateCount(80000, stack!, 5000, -1, plates);

        expect(edited?.total_g).toBe(70000);
        expect(edited?.per_side).toEqual([{ denomination_g: 25000, count: 1, colour: null }]);
    });

    it('rejects stacks beyond the mirrored inventory', () => {
        expect(
            loadFromPlateStack(
                80000,
                {
                    bar_g: 20000,
                    per_side: [{ denomination_g: 25000, count: 2 }],
                },
                plates,
            ),
        ).toBeNull();
    });

    it('returns bar only when target is light', () => {
        const load = nearestPlateLoad(20000, 20000, plates);
        expect(load?.per_side).toEqual([]);
        expect(load?.exact).toBe(true);
    });

    it('picks default bar', () => {
        expect(
            defaultBarG([
                { weight_g: 15000, is_default: false },
                { weight_g: 20000, is_default: true },
            ]),
        ).toBe(20000);
    });

    it('converts grams to kg', () => {
        expect(gramsToKg(20500)).toBe(20.5);
        expect(formatGramsToKg(60000)).toBe('60');
        expect(formatGramsToKg(62500)).toBe('62.5');
    });

    it('detects barbell equipment', () => {
        expect(usesBarbellPlates('barbell')).toBe(true);
        expect(usesBarbellPlates('dumbbell')).toBe(false);
    });
});
