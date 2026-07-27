import type { ExerciseOption } from '@/routines/types';

export function filterExercises(catalog: ExerciseOption[], query: string): ExerciseOption[] {
    const q = query.trim().toLowerCase();
    if (!q) {
        return catalog;
    }
    return catalog.filter(
        (e) =>
            e.name.toLowerCase().includes(q) ||
            (e.primary_muscle_group ?? '').toLowerCase().includes(q),
    );
}

/** Keep the current selection visible even when it falls outside the filter. */
export function exerciseOptionsFor(
    catalog: ExerciseOption[],
    filtered: ExerciseOption[],
    selectedId: number | null,
): ExerciseOption[] {
    if (selectedId == null || filtered.some((e) => e.id === selectedId)) {
        return filtered;
    }
    const selected = catalog.find((e) => e.id === selectedId);
    return selected ? [selected, ...filtered] : filtered;
}
