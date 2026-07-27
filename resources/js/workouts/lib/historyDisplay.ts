import type { PlayerSet } from '@/workouts/types';

/** Trim kg for display (supports 2dp loads like 28.75). */
export function formatKg(kg: number | null | undefined): string {
    if (kg == null) {
        return '—';
    }

    return String(parseFloat(kg.toFixed(2)));
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
