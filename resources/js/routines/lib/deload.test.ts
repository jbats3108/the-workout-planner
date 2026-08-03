import { formatDeloadSummary } from '@/routines/lib/deload';
import { describe, expect, it } from 'vitest';

describe('formatDeloadSummary', () => {
    it('formats weight, reps, and cadence', () => {
        expect(formatDeloadSummary(0.8, 0.8, 3)).toBe('0.8× weight · 0.8× reps · every 3');
    });

    it('formats disabled suggest as no suggest', () => {
        expect(formatDeloadSummary(0.5, 2, 0)).toBe('0.5× weight · 2× reps · no suggest');
    });
});
