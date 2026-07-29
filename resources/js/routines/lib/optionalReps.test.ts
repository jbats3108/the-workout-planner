import { optionalRepsPlaceholder, parseOptionalReps } from '@/routines/lib/optionalReps';
import { describe, expect, it } from 'vitest';

describe('optionalReps', () => {
    it('parses empty as null and numbers as numbers', () => {
        expect(parseOptionalReps('')).toBeNull();
        expect(parseOptionalReps('6')).toBe(6);
    });

    it('uses the user default as placeholder when set', () => {
        expect(optionalRepsPlaceholder(6)).toBe('6');
        expect(optionalRepsPlaceholder(null)).toBe('default');
        expect(optionalRepsPlaceholder(undefined)).toBe('default');
    });
});
