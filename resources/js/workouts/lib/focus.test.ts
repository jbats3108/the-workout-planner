import { playerBlock, playerSet } from '@/test/factories';
import { findFirstIncompleteFocus, flattenPlayerSets, setupKey } from '@/workouts/lib/focus';
import { describe, expect, it } from 'vitest';

describe('setupKey', () => {
    it('combines block id and phase', () => {
        expect(setupKey(5, 'after_block')).toBe('5:after_block');
    });
});

describe('flattenPlayerSets', () => {
    it('flattens sets with block index', () => {
        const blocks = [
            playerBlock({
                sets: [playerSet({ id: 1 }), playerSet({ id: 2, set_index: 1 })],
            }),
        ];
        expect(flattenPlayerSets(blocks)).toHaveLength(2);
    });
});

describe('findFirstIncompleteFocus', () => {
    it('returns first incomplete warm-up', () => {
        const blocks = [
            playerBlock({
                sets: [playerSet({ id: 1, group_type: 'warm_up', completed: false }), playerSet({ id: 2, group_type: 'working', completed: false })],
            }),
        ];
        expect(findFirstIncompleteFocus(blocks, {})).toEqual({ kind: 'set', blockIndex: 0, setId: 1 });
    });

    it('returns setup between warm-up and working', () => {
        const blocks = [
            playerBlock({
                has_setup_after_warm_up: true,
                sets: [playerSet({ id: 1, group_type: 'warm_up', completed: true }), playerSet({ id: 2, group_type: 'working', completed: false })],
            }),
        ];
        expect(findFirstIncompleteFocus(blocks, {})).toEqual({
            kind: 'setup',
            blockIndex: 0,
            phase: 'after_warm_up',
        });
    });

    it('returns done when everything complete', () => {
        const blocks = [
            playerBlock({
                sets: [playerSet({ id: 1, completed: true })],
            }),
        ];
        expect(findFirstIncompleteFocus(blocks, {})).toEqual({ kind: 'done' });
    });
});
