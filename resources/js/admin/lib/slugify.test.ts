import { describe, expect, it } from 'vitest';
import { slugify } from '@/admin/lib/slugify';

describe('slugify', () => {
    it('lowercases and hyphenates', () => {
        expect(slugify('Bench Press')).toBe('bench-press');
        expect(slugify('  Clean & Jerk  ')).toBe('clean-jerk');
    });
});
