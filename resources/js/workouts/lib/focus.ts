import type { Focus, PlayerBlock, SetupPhase } from '@/workouts/types';

export function setupKey(blockId: number, phase: SetupPhase): string {
    return `${blockId}:${phase}`;
}

export type FlatSetEntry = {
    blockIndex: number;
    block: PlayerBlock;
    set: PlayerBlock['sets'][number];
};

export function flattenPlayerSets(blocks: PlayerBlock[]): FlatSetEntry[] {
    return blocks.flatMap((block, blockIndex) => block.sets.map((set) => ({ blockIndex, block, set })));
}

export function findFirstIncompleteFocus(blocks: PlayerBlock[], setupDone: Record<string, boolean>): Focus {
    for (let blockIndex = 0; blockIndex < blocks.length; blockIndex++) {
        const block = blocks[blockIndex];
        const warmUps = block.sets.filter((s) => s.group_type === 'warm_up');
        const working = block.sets.filter((s) => s.group_type === 'working');

        const incompleteWarmUp = warmUps.find((s) => !s.completed);
        if (incompleteWarmUp) {
            return { kind: 'set', blockIndex, setId: incompleteWarmUp.id };
        }

        const hasIncompleteWorking = working.some((s) => !s.completed);
        if (block.has_setup_after_warm_up && warmUps.length > 0 && hasIncompleteWorking && !setupDone[setupKey(block.id, 'after_warm_up')]) {
            return { kind: 'setup', blockIndex, phase: 'after_warm_up' };
        }

        const incompleteWorking = working.find((s) => !s.completed);
        if (incompleteWorking) {
            return { kind: 'set', blockIndex, setId: incompleteWorking.id };
        }

        if (block.has_setup_after && !setupDone[setupKey(block.id, 'after_block')]) {
            return { kind: 'setup', blockIndex, phase: 'after_block' };
        }
    }
    return { kind: 'done' };
}
