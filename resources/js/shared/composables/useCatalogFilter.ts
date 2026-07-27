import { filterByQuery } from '@/shared/lib/catalogFilter';
import { computed, ref, type MaybeRefOrGetter, toValue } from 'vue';

export function useCatalogFilter<T>(
    catalog: MaybeRefOrGetter<T[]>,
    fields: (item: T) => string[],
) {
    const query = ref('');

    const filtered = computed(() => filterByQuery(toValue(catalog), query.value, fields));

    return { query, filtered };
}
