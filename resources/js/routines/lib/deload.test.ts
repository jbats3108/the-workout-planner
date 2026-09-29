import { formatDeloadSummary } from '@/routines/lib/deload';
import { describe, expect, it } from 'vitest';

describe('formatDeloadSummary', () => {
    it('formats cadence', () => {
        expect(formatDeloadSummary(3)).toBe('every 3');
    });

    it('formats disabled suggest as no suggest', () => {
        expect(formatDeloadSummary(0)).toBe('no suggest');
    });
});
