import type { ExerciseOption } from '@/routines/types';
import { filterByQuery } from '@/shared/lib/catalogFilter';

export function filterExercises(catalog: ExerciseOption[], query: string): ExerciseOption[] {
    return filterByQuery(catalog, query, (e) => [e.name, e.primary_muscle_group ?? '']);
}

/** Keep the current selection visible even when it falls outside the filter. */
export function exerciseOptionsFor(catalog: ExerciseOption[], filtered: ExerciseOption[], selectedId: number | null): ExerciseOption[] {
    if (selectedId == null || filtered.some((e) => e.id === selectedId)) {
        return filtered;
    }
    const selected = catalog.find((e) => e.id === selectedId);
    return selected ? [selected, ...filtered] : filtered;
}
