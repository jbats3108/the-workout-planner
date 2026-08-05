import { formatRest, normalizeRestSeconds } from '@/routines/lib/formatRest';
import { describe, expect, it } from 'vitest';

describe('formatRest', () => {
    it('formats seconds under a minute', () => {
        expect(formatRest(45)).toBe('45s');
    });

    it('formats minutes and seconds', () => {
        expect(formatRest(125)).toBe('2m 5s');
    });

    it('formats exact minutes', () => {
        expect(formatRest(120)).toBe('2m');
    });
});

describe('normalizeRestSeconds', () => {
    it('maps blank cleared inputs to 0', () => {
        expect(normalizeRestSeconds('')).toBe(0);
        expect(normalizeRestSeconds(null)).toBe(0);
        expect(normalizeRestSeconds(undefined)).toBe(0);
        expect(normalizeRestSeconds(Number.NaN)).toBe(0);
    });

    it('keeps valid rest values', () => {
        expect(normalizeRestSeconds(0)).toBe(0);
        expect(normalizeRestSeconds(90)).toBe(90);
        expect(normalizeRestSeconds('45')).toBe(45);
    });
});
