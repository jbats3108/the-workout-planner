import type { PlayerBlock, PlayerSet } from '@/workouts/types';
import type { FlatSetEntry } from '@/workouts/lib/focus';

export function previousSetWeightKg(entry: FlatSetEntry): number | null {
    const prior = entry.block.sets
        .filter(
            (s) =>
                s.workout_block_exercise_id === entry.set.workout_block_exercise_id &&
                s.group_type === entry.set.group_type &&
                s.set_index < entry.set.set_index &&
                s.completed &&
                s.logged_weight_kg != null,
        )
        .sort((a, b) => b.set_index - a.set_index)[0];

    return prior?.logged_weight_kg ?? null;
}

export function workingWeightForSet(entry: FlatSetEntry): number {
    const exercise = entry.block.exercises.find((e) => e.id === entry.set.workout_block_exercise_id);
    return exercise?.working_weight_kg ?? entry.set.target_weight_kg ?? 0;
}

export function shouldRestAfter(block: PlayerBlock, set: PlayerSet): boolean {
    if (!block.is_superset) {
        return true;
    }
    const sameIndex = block.sets.filter(
        (s) => s.set_index === set.set_index && s.group_type === set.group_type,
    );
    return sameIndex.every((s) => s.completed || s.id === set.id);
}

export function finishesWarmUpGroup(block: PlayerBlock, set: PlayerSet): boolean {
    if (set.group_type !== 'warm_up') {
        return false;
    }
    return block.sets
        .filter((s) => s.group_type === 'warm_up')
        .every((s) => s.completed || s.id === set.id);
}

export function workingRestSeconds(block: PlayerBlock): number {
    return block.sets.find((s) => s.group_type === 'working')?.rest_seconds ?? 0;
}

export function workingRoundsInBlock(block: PlayerBlock): number {
    const indexes = new Set(
        block.sets.filter((s) => s.group_type === 'working').map((s) => s.set_index),
    );
    return indexes.size;
}

export function nextDropSegmentWeight(lastKg: number): number {
    return Math.max(0, Math.round((lastKg - 2.5) * 2) / 2);
}

export function defaultPromoteSegments(workingKg: number): Array<{ weight_kg: number }> {
    return [
        { weight_kg: workingKg },
        { weight_kg: nextDropSegmentWeight(workingKg) },
    ];
}

export function visitLeavesWorkout(
    visit: { url: string | URL },
    workoutId: number,
): boolean {
    const url = typeof visit.url === 'string' ? new URL(visit.url, window.location.origin) : visit.url;
    return !url.pathname.startsWith(`/workouts/${workoutId}`);
}
