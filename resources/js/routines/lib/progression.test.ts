import { formatProgressionSummary } from '@/routines/lib/progression';
import { describe, expect, it } from 'vitest';

describe('formatProgressionSummary', () => {
    it('labels straight sets', () => {
        expect(formatProgressionSummary('straight_sets', 'ask')).toBe('Straight Sets');
    });

    it('labels progressive overload mid-block modes', () => {
        expect(formatProgressionSummary('progressive_overload', 'ask')).toBe('Progressive · Ask');
        expect(formatProgressionSummary('progressive_overload', 'auto')).toBe('Progressive · Auto');
    });
});
