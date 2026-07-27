import { describe, expect, it } from 'vitest';
import {
    defaultBarG,
    formatGramsToKg,
    gramsToKg,
    nearestPlateLoad,
    usesBarbellPlates,
} from '@/lib/plateCalculator';

describe('plateCalculator', () => {
    const plates = [
        { denomination_g: 20000, count: 2, colour: null },
        { denomination_g: 10000, count: 2, colour: null },
        { denomination_g: 5000, count: 2, colour: null },
    ];

    it('finds nearest loadable weight', () => {
        const load = nearestPlateLoad(80000, 20000, plates);
        expect(load?.total_g).toBe(80000);
        expect(load?.exact).toBe(true);
    });

    it('returns bar only when target is light', () => {
        const load = nearestPlateLoad(20000, 20000, plates);
        expect(load?.per_side).toEqual([]);
        expect(load?.exact).toBe(true);
    });

    it('picks default bar', () => {
        expect(defaultBarG([{ weight_g: 15000, is_default: false }, { weight_g: 20000, is_default: true }])).toBe(
            20000,
        );
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
