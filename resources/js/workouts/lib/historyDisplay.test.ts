import { formatKg } from '@/workouts/lib/historyDisplay';
import { describe, expect, it } from 'vitest';

describe('formatKg', () => {
    it('formats whole and 2dp weights', () => {
        expect(formatKg(80)).toBe('80');
        expect(formatKg(28.75)).toBe('28.75');
        expect(formatKg(28.7500001)).toBe('28.75');
        expect(formatKg(null)).toBe('—');
    });
});
