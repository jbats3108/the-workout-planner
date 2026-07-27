import { describe, expect, it } from 'vitest';
import { formatLoadStack, formatPlateStackLabel, resolvePlateLoad } from '@/workouts/lib/plates';
import { plateProfile } from '@/test/factories';

describe('resolvePlateLoad', () => {
    it('returns null for non-barbell equipment', () => {
        expect(resolvePlateLoad(100, 'dumbbell', plateProfile())).toBeNull();
    });

    it('resolves barbell load', () => {
        const load = resolvePlateLoad(100, 'barbell', plateProfile());
        expect(load).not.toBeNull();
        expect(load?.bar_g).toBe(20000);
    });
});

describe('formatPlateStackLabel', () => {
    it('formats bar-only stack', () => {
        const label = formatPlateStackLabel(
            { exact: true, total_g: 20000, bar_g: 20000, per_side: [], delta_g: 0 },
            'kg',
        );
        expect(label).toBe('20kg bar only');
    });
});

describe('formatLoadStack', () => {
    it('returns formatted stack for barbell', () => {
        const label = formatLoadStack('barbell', 100, plateProfile(), 'kg');
        expect(label).toContain('bar');
    });
});
