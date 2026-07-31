import type { PlayerBlock, PlayerSet } from '@/workouts/types';

/** Trim kg for display (supports 2dp loads like 28.75). */
export function formatKg(kg: number | null | undefined): string {
    if (kg == null) {
        return '—';
    }

    return String(parseFloat(kg.toFixed(2)));
}

/** Block heading for history: exercise name(s), A / B for supersets. */
export function historyBlockTitle(block: PlayerBlock): string {
    const names = [...block.exercises].sort((a, b) => a.position - b.position).map((exercise) => exercise.name);

    if (names.length === 0) {
        return `Block ${block.position}`;
    }

    if (block.is_superset && names.length >= 2) {
        return `${names[0]} / ${names[1]}`;
    }

    return names[0] ?? `Block ${block.position}`;
}

/** Warm-up loads for display: one group (null name) or per-exercise for supersets. */
export function historyWarmUpGroups(sets: PlayerSet[]): Array<{ exerciseName: string | null; loads: string[] }> {
    if (sets.length === 0) {
        return [];
    }

    const multi = new Set(sets.map((set) => set.exercise_name)).size > 1;
    if (!multi) {
        return [
            {
                exerciseName: null,
                loads: sets.map((set) => `${set.logged_reps ?? '—'}×${formatKg(set.logged_weight_kg ?? set.target_weight_kg)}`),
            },
        ];
    }

    const groups = new Map<number, { exerciseName: string; loads: string[] }>();
    for (const set of sets) {
        const load = `${set.logged_reps ?? '—'}×${formatKg(set.logged_weight_kg ?? set.target_weight_kg)}`;
        const existing = groups.get(set.workout_block_exercise_id);
        if (existing) {
            existing.loads.push(load);
            continue;
        }
        groups.set(set.workout_block_exercise_id, { exerciseName: set.exercise_name, loads: [load] });
    }

    return [...groups.values()].map((group) => ({ exerciseName: group.exerciseName, loads: group.loads }));
}

export type HistoryBlockRow =
    | { type: 'warm_up'; key: string; sets: PlayerSet[] }
    | { type: 'working_group'; key: string; exerciseName: string; sets: PlayerSet[] };

/** One warm-up cluster + one working group per exercise (set order preserved). */
export function historyRowsForBlock(sets: PlayerSet[]): HistoryBlockRow[] {
    const rows: HistoryBlockRow[] = [];
    const warmUps = sets.filter((set) => set.group_type === 'warm_up');
    const working = sets.filter((set) => set.group_type === 'working');

    if (warmUps.length > 0) {
        rows.push({ type: 'warm_up', key: `warm_up-${warmUps[0].id}`, sets: warmUps });
    }

    const groups = new Map<number, PlayerSet[]>();
    for (const set of working) {
        const existing = groups.get(set.workout_block_exercise_id);
        if (existing) {
            existing.push(set);
            continue;
        }
        groups.set(set.workout_block_exercise_id, [set]);
    }

    for (const [exerciseId, groupSets] of groups) {
        rows.push({
            type: 'working_group',
            key: `working-${exerciseId}`,
            exerciseName: groupSets[0].exercise_name,
            sets: [...groupSets].sort((a, b) => a.set_index - b.set_index),
        });
    }

    return rows;
}
