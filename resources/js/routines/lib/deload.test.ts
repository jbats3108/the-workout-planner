import { formatDeloadSummary } from '@/routines/lib/deload';
import { describe, expect, it } from 'vitest';

describe('formatDeloadSummary', () => {
    it('formats weight and reps factors', () => {
        expect(formatDeloadSummary(0.8, 0.8)).toBe('0.8× weight · 0.8× reps');
    });
});
