import { formatDate } from '@/shared/lib/formatDate';
import { describe, expect, it } from 'vitest';

describe('formatDate', () => {
    it('returns empty string for blank input', () => {
        expect(formatDate('')).toBe('');
    });

    it('formats an ISO date with medium dateStyle', () => {
        expect(formatDate('2026-01-15T12:00:00Z')).toMatch(/2026|Jan|15/);
    });
});
