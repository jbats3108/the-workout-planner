import { describe, expect, it } from 'vitest';
import { filterByQuery } from '@/shared/lib/catalogFilter';

describe('filterByQuery', () => {
    const items = [
        { name: 'Bench Press', slug: 'bench-press', group: 'Chest' },
        { name: 'Row', slug: 'row', group: 'Back' },
    ];

    it('filters across configured fields', () => {
        expect(filterByQuery(items, 'bench', (i) => [i.name, i.slug, i.group])).toHaveLength(1);
        expect(filterByQuery(items, '', (i) => [i.name])).toHaveLength(2);
    });
});
