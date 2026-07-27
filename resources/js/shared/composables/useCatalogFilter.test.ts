import { useCatalogFilter } from '@/shared/composables/useCatalogFilter';
import { describe, expect, it } from 'vitest';
import { ref } from 'vue';

describe('useCatalogFilter', () => {
    it('filters reactive catalog', () => {
        const catalog = ref([{ name: 'Bench' }, { name: 'Row' }]);
        const { query, filtered } = useCatalogFilter(catalog, (item) => [item.name]);
        query.value = 'row';
        expect(filtered.value).toHaveLength(1);
    });
});
