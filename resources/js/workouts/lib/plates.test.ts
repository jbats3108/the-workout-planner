import { plateProfile } from '@/test/factories';
import {
    changePlateCount,
    formatLoadStack,
    formatPlateStackLabel,
    resolvePlateLoad,
    resolvePlateStack,
    serializePlateStack,
} from '@/workouts/lib/plates';
import { describe, expect, it } from 'vitest';

describe('resolvePlateLoad', () => {
    it('returns null for non-barbell equipment', () => {
        expect(resolvePlateLoad(100, 'dumbbell', plateProfile())).toBeNull();
    });

    it('resolves barbell load', () => {
        const load = resolvePlateLoad(100, 'barbell', plateProfile());
        expect(load).not.toBeNull();
        expect(load?.bar_g).toBe(20000);
    });

    it('resolves an edited stack', () => {
        const load = resolvePlateStack(80, 'barbell', plateProfile(), {
            bar_g: 20000,
            per_side: [
                { denomination_g: 20000, count: 1 },
                { denomination_g: 10000, count: 1 },
            ],
        });

        expect(load?.total_g).toBe(80000);
        expect(serializePlateStack(load!)).toEqual({
            bar_g: 20000,
            per_side: [
                { denomination_g: 20000, count: 1 },
                { denomination_g: 10000, count: 1 },
            ],
        });
    });

    it('changes one side plate while preserving the target reference', () => {
        const load = resolvePlateLoad(80, 'barbell', plateProfile());
        const edited = changePlateCount(80, load!, 10000, -1, plateProfile());

        expect(edited?.total_g).toBe(60000);
        expect(edited?.delta_g).toBe(-20000);
    });
});

describe('formatPlateStackLabel', () => {
    it('formats bar-only stack', () => {
        const label = formatPlateStackLabel({ exact: true, total_g: 20000, bar_g: 20000, per_side: [], delta_g: 0 }, 'kg');
        expect(label).toBe('20kg bar only');
    });
});

describe('formatLoadStack', () => {
    it('returns formatted stack for barbell', () => {
        const label = formatLoadStack('barbell', 100, plateProfile(), 'kg');
        expect(label).toContain('bar');
    });
});
