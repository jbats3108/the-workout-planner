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

export type HistoryBlockRow = { type: 'warm_up'; key: string; sets: PlayerSet[] } | { type: 'working'; key: string; set: PlayerSet };

/** Collapse contiguous warm-up sets into one row; working sets stay individual. */
export function historyRowsForBlock(sets: PlayerSet[]): HistoryBlockRow[] {
    const rows: HistoryBlockRow[] = [];
    let warmUps: PlayerSet[] = [];

    const flushWarmUps = () => {
        if (warmUps.length === 0) {
            return;
        }

        rows.push({ type: 'warm_up', key: `warm_up-${warmUps[0].id}`, sets: warmUps });
        warmUps = [];
    };

    for (const set of sets) {
        if (set.group_type === 'warm_up') {
            warmUps.push(set);
            continue;
        }

        flushWarmUps();
        rows.push({ type: 'working', key: `working-${set.id}`, set });
    }

    flushWarmUps();

    return rows;
}
