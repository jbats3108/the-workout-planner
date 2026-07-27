import { describe, expect, it } from 'vitest';
import { formatRest } from '@/routines/lib/formatRest';

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
