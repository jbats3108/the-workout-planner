import { ref } from 'vue';
import { describe, expect, it } from 'vitest';
import { useCatalogFilter } from '@/shared/composables/useCatalogFilter';

describe('useCatalogFilter', () => {
    it('filters reactive catalog', () => {
        const catalog = ref([{ name: 'Bench' }, { name: 'Row' }]);
        const { query, filtered } = useCatalogFilter(catalog, (item) => [item.name]);
        query.value = 'row';
        expect(filtered.value).toHaveLength(1);
    });
});
