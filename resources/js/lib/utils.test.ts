import { cn } from '@/lib/utils';
import { describe, expect, it } from 'vitest';

describe('cn', () => {
    it('merges tailwind classes', () => {
        expect(cn('px-2', 'px-4')).toBe('px-4');
        expect(cn('text-sm', false && 'hidden', 'font-bold')).toBe('text-sm font-bold');
    });
});
