import type { Block, WarmUpStep } from '@/routines/types';

export function warmUpText(block: Block): string {
    return block.warm_up.steps.map((s) => `${s.percent}x${s.reps}`).join(', ');
}

export function syncWarmUpMeta(block: Block): void {
    block.warm_up.set_count = block.warm_up.steps.length;
    if (block.warm_up.steps.length === 0) {
        block.has_setup_after_warm_up = false;
    }
}

function normalizeWarmUpStep(step: WarmUpStep): WarmUpStep {
    return {
        percent: step.percent,
        reps: step.reps,
        has_setup_after: step.has_setup_after ?? false,
    };
}

/** Compact editor string: `40x5, 60x3, 80x1` (also accepts legacy `40, 60, 80`). */
export function setWarmUpText(block: Block, value: string): void {
    const previousFlags = block.warm_up.steps.map((s) => s.has_setup_after ?? false);
    block.warm_up.steps = value
        .split(',')
        .map((part) => part.trim())
        .filter(Boolean)
        .map((part) => {
            const withReps = part.match(/^(\d+)\s*[x×]\s*(\d+)$/i);
            if (withReps) {
                return { percent: parseInt(withReps[1], 10), reps: parseInt(withReps[2], 10) };
            }
            const percentOnly = parseInt(part, 10);
            if (!Number.isNaN(percentOnly) && percentOnly > 0) {
                return { percent: percentOnly, reps: 5 };
            }
            return null;
        })
        .filter((s): s is Omit<WarmUpStep, 'has_setup_after'> => s !== null && s.percent > 0 && s.reps > 0)
        .map((step, index) => normalizeWarmUpStep({ ...step, has_setup_after: previousFlags[index] ?? false }));
    syncWarmUpMeta(block);
}

export function addWarmUpStep(block: Block): void {
    block.warm_up.steps.push({ percent: 50, reps: 5, has_setup_after: false });
    syncWarmUpMeta(block);
}

export function removeWarmUpStep(block: Block, index: number): void {
    block.warm_up.steps.splice(index, 1);
    syncWarmUpMeta(block);
}

export function clearWarmUp(block: Block): void {
    block.warm_up.steps = [];
    syncWarmUpMeta(block);
}

export function canSetupAfterWarmUpStep(block: Block, stepIndex: number): boolean {
    return stepIndex < block.warm_up.steps.length - 1;
}
